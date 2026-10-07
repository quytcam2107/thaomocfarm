<?php

declare(strict_types=1);

namespace App\Requests;

use App\Models\Ward;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[0-9]{9,11}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            // Địa chính 2 cấp: tỉnh/thành (provinces.code) + xã/phường (wards.code)
            'province_code' => ['required', 'integer', 'exists:provinces,code'],
            'ward_code' => ['required', 'integer', 'exists:wards,code'],
            'address' => ['required', 'string', 'max:255'],
            'shipping_method' => ['required', 'in:fast,standard'],
            'payment_method' => ['required', 'in:cod'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Đảm bảo xã/phường thực sự thuộc tỉnh/thành đã chọn (chống ghép cặp lệch
     * khi client can thiệp value). Chạy sau khi province_code/ward_code đã tồn tại.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $provinceCode = $this->input('province_code');
                $wardCode = $this->input('ward_code');

                // Chỉ cross-check khi cả hai đã qua rule exists (tránh báo lỗi chồng)
                if ($validator->errors()->has('province_code') || $validator->errors()->has('ward_code')) {
                    return;
                }

                if ($provinceCode === null || $wardCode === null) {
                    return;
                }

                $belongs = Ward::query()
                    ->where('code', (int) $wardCode)
                    ->where('province_code', (int) $provinceCode)
                    ->exists();

                if (!$belongs) {
                    $validator->errors()->add('ward_code', 'Xã/phường không thuộc tỉnh/thành đã chọn.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ tên.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại không hợp lệ (từ 9 đến 11 số).',
            'email.email' => 'Email không hợp lệ.',
            'province_code.required' => 'Vui lòng chọn tỉnh/thành phố.',
            'province_code.integer' => 'Tỉnh/thành phố không hợp lệ.',
            'province_code.exists' => 'Tỉnh/thành phố không tồn tại.',
            'ward_code.required' => 'Vui lòng chọn xã/phường.',
            'ward_code.integer' => 'Xã/phường không hợp lệ.',
            'ward_code.exists' => 'Xã/phường không tồn tại.',
            'address.required' => 'Vui lòng nhập địa chỉ chi tiết.',
            'shipping_method.required' => 'Vui lòng chọn phương thức vận chuyển.',
            'shipping_method.in' => 'Phương thức vận chuyển không hợp lệ.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method.in' => 'Hiện tại chỉ hỗ trợ thanh toán COD.',
        ];
    }
}
