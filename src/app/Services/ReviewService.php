<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ReviewStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/*
 * ReviewService — tầng nghiệp vụ đánh giá sản phẩm (PDP).
 * - require_verified_purchase / auto_approve đọc config('thaomoc.review').
 * - "Đã mua" = từng có đơn delivered chứa sản phẩm, khớp user_id HOẶC
 *   customer_email (khách đặt dạng guest — orders.user_id đang NULL với 6 đơn thật).
 * - Ghi xong bump cache group 'review' (remember_group driver file — cấm tags()).
 */
class ReviewService
{
    /**
     * Khách này (user hoặc IP) đã từng nhận hàng thành công sản phẩm?
     */
    public function hasPurchased(int $productId, ?int $userId, ?string $email): bool
    {
        if ($userId === null && ($email === null || $email === '')) {
            return false;
        }

        return DB::table('orders')
            ->whereExists(function ($q) use ($productId): void {
                $q->select(DB::raw(1))
                    ->from('order_items')
                    ->whereColumn('order_items.order_id', 'orders.id')
                    ->where('order_items.product_id', $productId);
            })
            ->where('status', OrderStatus::DELIVERED->value)
            ->where(function ($q) use ($userId, $email): void {
                if ($userId !== null) {
                    $q->orWhere('user_id', $userId);
                }
                if ($email !== null && $email !== '') {
                    $q->orWhere('customer_email', strtolower($email));
                }
            })
            ->exists();
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
     * Tạo đánh giá từ request PDP. Trả về message tiếng Việt cho client.
     *
     * @param array{rating:int, content:string, name?:string|null, email?:string|null} $data
     */
    public function store(Product $product, Request $request, array $data): string
    {
        $userId = $request->user()?->id;
        $ip = (string) $request->ip();
        $email = strtolower(trim((string) ($data['email'] ?? ''))) ?: $request->user()?->email;

        // Chống spam: 1 người (user/IP) chỉ 1 review cho 1 sản phẩm
        if ($this->alreadyReviewed((int) $product->id, $userId, $ip)) {
            throw new \RuntimeException('Bạn đã đánh giá sản phẩm này rồi.');
        }

        // Config require_verified_purchase=true: chỉ người từng mua mới được ghi
        if (
            (bool) config('thaomoc.review.require_verified_purchase', true)
            && !$this->hasPurchased((int) $product->id, $userId, $email)
        ) {
            throw new \RuntimeException('Chỉ khách hàng đã mua sản phẩm mới có thể đánh giá. Vui lòng kiểm tra lại email/SĐT đã dùng khi đặt hàng.');
        }

        $isVerified = true; // đã lọt vòng kiểm tra mua hàng ở trên

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $userId,
            'order_id' => null, // giữ unique(order_id,product_id) an toàn với NULL
            'ip_address' => $ip,
            'rating' => max(1, min(5, (int) $data['rating'])),
            'content' => trim((string) $data['content']),
            'is_verified' => $isVerified,
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
}