<?php

declare(strict_types=1);

namespace App\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyCouponRequest extends FormRequest
{
    /**
     * Guest lẫn user đều được áp mã ở giỏ hàng.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Chuẩn hoá mã: trim + uppercase trước khi validate.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code', ''))),
        ]);
    }

    /**
     * Rules cho mã giảm giá.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Z0-9\-]+$/'],
        ];
    }

    /**
     * Message tiếng Việt.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Vui lòng nhập mã giảm giá.',
            'code.min' => 'Mã giảm giá không hợp lệ.',
            'code.max' => 'Mã giảm giá không hợp lệ.',
            'code.regex' => 'Mã giảm giá chỉ gồm chữ in hoa, số và gạch ngang.',
        ];
    }
}