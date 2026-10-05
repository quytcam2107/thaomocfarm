<?php

declare(strict_types=1);

namespace App\Requests;

use App\Services\ReviewService;
use Illuminate\Foundation\Http\FormRequest;

/*
 * Validate đánh giá gửi từ PDP. Messages tiếng Việt theo style AddToCartRequest.
 * slug là route param (product_id lấy theo slug trong controller — không trust id thô).
 */
class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string|min:10|max:1000',
            // Danh tính hiển thị — tên nên nhập nhưng vẫn nullable (service fallback)
            'name' => 'nullable|string|max:100',
            // FIX YÊU CẦU MỚI: SĐT là nguồn đối chiếu "đã mua" (thay email) —
            // cùng regex 9..11 chữ số với CheckoutRequest để khớp orders.customer_phone
            'phone' => ['required', 'string', 'regex:/^[0-9+ ]{9,15}$/'],
            // Email giữ trên form nhưng KHÔNG bắt buộc nữa, chỉ format-check khi điền
            'email' => 'nullable|email:rfc|max:150',
            // NEW: ảnh đánh giá — tối đa 5 file, mỗi file ≤ config upload (2MB)
            'images' => 'nullable|array|max:' . ReviewService::MAX_IMAGES,
            'images.*' => [
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:' . (int) config('thaomoc.upload.max_size_kb', 2048),
            ],
        ];
    }

    /**
     * Cho phép client xóa ảnh đã chọn bằng cách gửi images[] = "" (input
     * type=file trong DataTransfer rỗng sinh ra phần tử empty) — loại trước
     * khi validate để không báo lỗi "the image field must be a file".
     */
    protected function prepareForValidation(): void
    {
        // Trim sẵn để service không phải xử lý khoảng trắng thừa
        $this->merge([
            'name' => $this->filled('name') ? trim((string) $this->input('name')) : null,
            'email' => $this->filled('email') ? trim((string) $this->input('email')) : null,
            'phone' => $this->filled('phone') ? trim((string) $this->input('phone')) : null,
            'content' => trim((string) $this->input('content', '')),
        ]);

        // Lọc phần tử rỗng khỏi mảng files (khách bấm chọn rồi bỏ hết ảnh)
        if (is_array($this->input('images'))) {
            $files = array_values(array_filter(
                $this->file('images', []),
                fn($f) => $f instanceof \Illuminate\Http\UploadedFile && $f->isValid()
            ));
            $this->request->set('images', $files);
        }
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'rating.required' => 'Vui lòng chọn số sao đánh giá',
            'rating.min' => 'Số sao phải từ 1 đến 5',
            'rating.max' => 'Số sao phải từ 1 đến 5',
            'content.required' => 'Vui lòng viết nhận xét của bạn',
            'content.min' => 'Nhận xét cần tối thiểu 10 ký tự',
            'content.max' => 'Nhận xét tối đa 1000 ký tự',
            'name.max' => 'Tên hiển thị tối đa 100 ký tự',
            // SĐT bắt buộc vì là mắt xích đối chiếu đơn hàng
            'phone.required' => 'Vui lòng nhập số điện thoại đã dùng khi đặt hàng',
            'phone.regex' => 'Số điện thoại không hợp lệ (từ 9 đến 11 chữ số)',
            'email.email' => 'Email không hợp lệ',
            'email.max' => 'Email tối đa 150 ký tự',
            'images.max' => 'Chỉ được tải tối đa ' . ReviewService::MAX_IMAGES . ' ảnh',
            'images.*.mimes' => 'Ảnh chỉ chấp nhận định dạng JPG, PNG hoặc WEBP',
            'images.*.max' => 'Mỗi ảnh tối đa ' . round(((int) config('thaomoc.upload.max_size_kb', 2048)) / 1024, 1) . 'MB',
        ];
    }
}