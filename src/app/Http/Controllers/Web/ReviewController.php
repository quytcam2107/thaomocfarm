<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Requests\StoreReviewRequest;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/*
 * ReviewController — endpoint AJAX cho khối đánh giá trên PDP.
 * Route đặt TRƯỚC catch-all /{slug}; tên route prefix web.;
 * Rate limit theo convention dự án: gọi facade RateLimiter trực tiếp
 * (giống apply_coupon 10/phút — ở đây 3/phút/IP chống spam review).
 */
class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewService $reviewService
    ) {
    }

    /**
     * POST /san-pham/{slug}/danh-gia  (web.product.reviews.store)
     */
    public function store(StoreReviewRequest $request, string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)->where('status', 'active')->first();
        if ($product === null) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
        }

        // Rate limit 3 lần/phút/IP — key theo convention 'action|ip'
        $key = 'store_review|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn thao tác quá nhanh, vui lòng thử lại sau ít phút.',
            ], 429);
        }
        RateLimiter::hit($key, 60);

        try {
            $message = $this->reviewService->store($product, $request, $request->validated());
            RateLimiter::clear($key); // thành công vẫn trừ... giữ nguyên limiter để chống spam dây chuyền
            return response()->json(['success' => true, 'message' => $message]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại.'], 500);
        }
    }

    /**
     * POST /danh-gia/{review}/huu-ich  (web.review.helpful)
     */
    public function helpful(Request $request, int $reviewId): JsonResponse
    {
        $key = 'review_helpful|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 20)) {
            return response()->json(['success' => false, 'message' => 'Thao tác quá nhanh, thử lại sau nhé.'], 429);
        }
        RateLimiter::hit($key, 60);

        $count = $this->reviewService->markHelpful($reviewId, (string) $request->ip());

        return response()->json(['success' => true, 'helpful_count' => $count]);
    }

    /**
     * NEW: POST /danh-gia/{review}/phan-hoi  (web.review.reply)
     * Admin/staff phản hồi công khai 1 đánh giá (reply rỗng = xóa phản hồi).
     * Quyền theo convention dự án: kiểm tra user->isStaff() trực tiếp trong
     * controller (app chưa đăng ký Gate/Policy nào — xem User::isStaff()).
     */
    public function reply(Request $request, int $reviewId): JsonResponse
    {
        $user = $request->user();
        if ($user === null || !$user->isStaff()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền phản hồi đánh giá.'], 403);
        }

        // Reply bắt buộc khi gửi từ UI admin; rỗng = xóa (service xử lý)
        $data = $request->validate([
            'admin_reply' => 'nullable|string|max:1000',
        ], [
            'admin_reply.max' => 'Phản hồi tối đa 1000 ký tự',
        ]);

        $review = Review::find($reviewId);
        if ($review === null) {
            return response()->json(['success' => false, 'message' => 'Đánh giá không tồn tại'], 404);
        }

        $updated = $this->reviewService->reply($review, (string) ($data['admin_reply'] ?? ''));

        return response()->json([
            'success' => true,
            'message' => $updated->admin_reply ? 'Đã lưu phản hồi.' : 'Đã xóa phản hồi.',
            'admin_reply' => $updated->admin_reply,
        ]);
    }
}