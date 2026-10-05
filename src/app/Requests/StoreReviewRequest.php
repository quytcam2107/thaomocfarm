<?php

declare(strict_types=1);

namespace App\Requests;

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
            // Guest bắt buộc danh tính để đối chiếu đơn hàng (xem ReviewService::hasPurchased)
            'name' => 'nullable|string|max:100',
            'email' => 'nullable|email:rfc|max:150',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'rating.required' => 'Vui lòng chọn số sao đánh giá',
            'rating.between' => 'Số sao phải từ 1 đến 5',
            'content.required' => 'Vui lòng viết nhận xét của bạn',
            'content.min' => 'Nhận xét cần tối thiểu 10 ký tự',
            'content.max' => 'Nhận xét tối đa 1000 ký tự',
            'name.max' => 'Tên hiển thị tối đa 100 ký tự',
            'email.email' => 'Email không hợp lệ',
            'email.max' => 'Email tối đa 150 ký tự',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Trim sẵn để service không phải xử lý khoảng trắng thừa
        $this->merge([
            'name' => $this->filled('name') ? trim((string) $this->input('name')) : null,
            'email' => $this->filled('email') ? trim((string) $this->input('email')) : null,
            'content' => trim((string) $this->input('content', '')),
        ]);
    }
}