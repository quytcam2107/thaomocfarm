<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    /**
     * Xác thực dữ liệu thêm giỏ hàng.
     */
    public function authorize(): bool
    {
        return true; // Guest cũng được phép thêm giỏ
    }

    /**
     * Quy tắc xác thực.
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'qty' => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }

    /**
     * Thông báo lỗi tùy chỉnh.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Vui lòng chọn sản phẩm.',
            'product_id.exists' => 'Sản phẩm không tồn tại.',
            'variant_id.exists' => 'Phiên bản sản phẩm không tồn tại.',
            'qty.required' => 'Vui lòng nhập số lượng.',
            'qty.integer' => 'Số lượng không hợp lệ.',
            'qty.min' => 'Số lượng tối thiểu là 1.',
            'qty.max' => 'Số lượng tối đa là 999.',
        ];
    }
}