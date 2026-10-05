<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ReviewStatus;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewVote;
use App\Services\Review\ReviewPhoneMatcher;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/*
 * ReviewService — tầng nghiệp vụ đánh giá sản phẩm (PDP).
 * - require_verified_purchase / auto_approve đọc config('thaomoc.review').
 * - FIX YÊU CẦU MỚI: "Đã mua" đối chiếu bằng SỐ ĐIỆN THOẠI (khớp
 *   orders.customer_phone của đơn delivered chứa sản phẩm — xem
 *   ReviewPhoneMatcher). Email vẫn giữ trên form nhưng KHÔNG bắt buộc,
 *   chỉ là tham chiếu phụ khi khách điền.
 * - NEW: ảnh đánh giá — tối đa 5 file, lưu public/assets/images/reviews/,
 *   mảng đường dẫn tương đối vào cột json `reviews.images` (cột THẬT, xem
 *   ai-database/tables/reviews.md).
 * - NEW: admin_reply từng đánh giá — reply() cập nhật cột text `admin_reply`.
 * - Ghi xong bump cache group 'review' (remember_group driver file — cấm tags()).
 */
class ReviewService
{
    /** Số ảnh tối đa 1 đánh giá được tải lên */
    public const MAX_IMAGES = 5;

    /** Thư mục chứa ảnh review (đường dẫn DB lưu relative theo folder này) */
    public const IMAGE_FOLDER = 'assets/images/reviews';

    /**
     * Khách này (user / SĐT / email) đã từng nhận hàng thành công sản phẩm?
     * Ưu tiên 1: user_id trên đơn delivered. Ưu tiên 2 (NGUỒN THẬT MỚI): SĐT
     * khớp orders.customer_phone. Ưu tiên 3 (fallback lịch sử): email từng
     * dùng đặt đơn — email không còn bắt buộc nên chỉ dùng khi khách điền.
     */
    public function hasPurchased(int $productId, ?int $userId, ?string $email, ?string $phone = null): bool
    {
        // 1) Khách login: khớp user_id trên đơn delivered chứa sản phẩm
        if ($userId !== null) {
            $byUser = DB::table('orders')
                ->whereExists(function ($q) use ($productId): void {
                    $q->select(DB::raw(1))
                        ->from('order_items')
                        ->whereColumn('order_items.order_id', 'orders.id')
                        ->where('order_items.product_id', $productId);
                })
                ->where('status', OrderStatus::DELIVERED->value)
                ->where('user_id', $userId)
                ->exists();

            if ($byUser) {
                return true;
            }
        }

        // 2) Đối chiếu SĐT (yêu cầu mới — thay vai trò chính của email)
        $normalized = ReviewPhoneMatcher::normalize($phone);
        if ($normalized !== null && ReviewPhoneMatcher::findDeliveredOrder($productId, $normalized) !== null) {
            return true;
        }

        // 3) Fallback lịch sử: email từng dùng khi đặt đơn (nullable)
        if ($email !== null && $email !== '') {
            return DB::table('orders')
                ->whereExists(function ($q) use ($productId): void {
                    $q->select(DB::raw(1))
                        ->from('order_items')
                        ->whereColumn('order_items.order_id', 'orders.id')
                        ->where('order_items.product_id', $productId);
                })
                ->where('status', OrderStatus::DELIVERED->value)
                ->where('customer_email', strtolower($email))
                ->exists();
        }

        return false;
    }

    /**
     * Chặn khách vãng lai (theo IP) đánh giá trùng sản phẩm.
     * User đã login: chặn theo user_id (kể cả khi phát sinh auth trong tương lai).
     */
    public function alreadyReviewed(int $productId, ?int $userId, string $ip): bool
    {
        return Review::query()
            ->where('product_id', $productId)
            ->when(
                $userId !== null,
                fn($q) => $q->where('user_id', $userId),
                fn($q) => $q->where('ip_address', $ip)
            )
            ->whereIn('status', [ReviewStatus::PENDING->value, ReviewStatus::APPROVED->value])
            ->exists();
    }

    /**
     * Tạo đánh giá từ request PDP (multipart/form-data vì có ảnh).
     * Trả về message tiếng Việt cho client.
     *
     * @param array{rating:int, content:string, name?:string|null, phone?:string|null, email?:string|null} $data
     */
    public function store(Product $product, Request $request, array $data): string
    {
        $userId = $request->user()?->id;
        $ip = (string) $request->ip();
        $name = trim((string) ($data['name'] ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? ''))) ?: $request->user()?->email;
        // SĐT nhập tay; fallback SĐT tài khoản login nếu có
        $phone = ReviewPhoneMatcher::normalize($data['phone'] ?? null)
            ?? ReviewPhoneMatcher::normalize($request->user()?->phone ?? null);

        // Chống spam: 1 người (user/IP) chỉ 1 review cho 1 sản phẩm
        if ($this->alreadyReviewed((int) $product->id, $userId, $ip)) {
            throw new \RuntimeException('Bạn đã đánh giá sản phẩm này rồi.');
        }

        // SĐT là mắt xích đối chiếu bắt buộc sau khi bỏ bắt buộc email
        if ($phone === null) {
            throw new \RuntimeException('Vui lòng nhập số điện thoại đã dùng khi đặt hàng (9–11 chữ số).');
        }

        // Config require_verified_purchase=true: chỉ người từng mua mới được ghi
        if (
            (bool) config('thaomoc.review.require_verified_purchase', true)
            && !$this->hasPurchased((int) $product->id, $userId, $email, $phone)
        ) {
            throw new \RuntimeException('Không tìm thấy đơn đã giao của bạn cho sản phẩm này. Vui lòng kiểm tra lại số điện thoại đã dùng khi đặt hàng.');
        }

        // Xử lý ảnh (tối đa MAX_IMAGES; lỗi file → RuntimeException → HTTP 422)
        $imagePaths = $this->storeImages($request->file('images', []));

        $review = Review::create([
            'product_id' => $product->id,
            // user_id chỉ ghi khi khách đăng nhập; review guest để NULL
            // (migration 000002 đã nới cột này thành nullable)
            'user_id' => $userId ?: null,
            'ip_address' => $ip,
            // NEW: danh tính tự khai — tên + SĐT đối chiếu đơn (customer_name/customer_phone)
            'customer_name' => $name !== '' ? $name : ($request->user()?->name ?: 'Ẩn danh'),
            'customer_phone' => $phone,
            'order_id' => null, // giữ unique(order_id,product_id) an toàn với NULL
            'rating' => max(1, min(5, (int) $data['rating'])),
            'content' => trim((string) $data['content']),
            'images' => $imagePaths !== [] ? $imagePaths : null,
            'is_verified' => true, // đã lọt vòng kiểm tra mua hàng ở trên
            'status' => (bool) config('thaomoc.review.auto_approve', false)
                ? ReviewStatus::APPROVED->value
                : ReviewStatus::PENDING->value,
        ]);

        // Sản phẩm vừa có review mới -> vô hiệu cache group 'review' ngay
        bump_group_version('review');

        return $review->status === ReviewStatus::APPROVED->value
            ? 'Cảm ơn bạn! Đánh giá đã được hiển thị.'
            : 'Cảm ơn bạn! Đánh giá đã được ghi nhận và sẽ hiển thị sau khi được duyệt.';
    }

    /**
     * NEW: lưu ảnh đánh giá vào public/assets/images/reviews/.
     * Validate lại mimes/size theo config('thaomoc.upload') (dù FormRequest
     * đã chặn) — phòng call-site khác dùng trực tiếp service. Trả về list
     * đường dẫn tương đối ('assets/images/reviews/xxx.jpg') để view bọc asset().
     *
     * @param array<int, UploadedFile>|null $files
     * @return list<string>
     */
    private function storeImages(?array $files): array
    {
        $files = array_values(array_filter((array) $files, fn($f) => $f instanceof UploadedFile));

        if (count($files) > self::MAX_IMAGES) {
            throw new \RuntimeException('Chỉ được tải tối đa ' . self::MAX_IMAGES . ' ảnh cho 1 đánh giá.');
        }

        if ($files === []) {
            return [];
        }

        $maxKb = (int) config('thaomoc.upload.max_size_kb', 2048);
        $allowed = (array) config('thaomoc.upload.allowed_mimes', ['image/jpeg', 'image/png', 'image/webp']);

        $dir = public_path(self::IMAGE_FOLDER);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            report(new \RuntimeException('Không tạo được thư mục ' . $dir));
            throw new \RuntimeException('Hệ thống đang bận, vui lòng gửi lại sau.');
        }

        $paths = [];
        foreach ($files as $file) {
            $mime = (string) $file->getMimeType();
            if (!in_array($mime, $allowed, true)) {
                throw new \RuntimeException('Ảnh chỉ chấp nhận định dạng JPG, PNG hoặc WEBP.');
            }
            if ($file->getSize() > $maxKb * 1024) {
                throw new \RuntimeException('Mỗi ảnh tối đa ' . round($maxKb / 1024, 1) . 'MB.');
            }

            $name = ReviewPhoneMatcher::makeFileName($file);
            // Tên đã random 16 hex nên không lo trùng file cũ
            $file->move($dir, $name);
            $paths[] = self::IMAGE_FOLDER . '/' . $name;
        }

        return $paths;
    }

    /**
     * Bấm "Hữu ích". UNIQUE(review_id, ip) -> insertOrIgnore chặn trùng nguyên tử.
     */
    public function markHelpful(int $reviewId, string $ip): int
    {
        ReviewVote::query()->insertOrIgnore([
            'review_id' => $reviewId,
            'ip_address' => $ip,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Đếm lại từ DB (không tin số client gửi lên)
        return ReviewVote::query()->where('review_id', $reviewId)->count();
    }

    /**
     * NEW: quản trị phản hồi 1 đánh giá (reply công khai hiển thị bên dưới
     * review trên PDP). Reply rỗng = xóa phản hồi. Bump cache 'review'.
     */
    public function reply(Review $review, string $reply): Review
    {
        $trimmed = trim($reply);
        $review->admin_reply = $trimmed !== '' ? $trimmed : null;
        $review->save();

        bump_group_version('review');

        return $review;
    }
}