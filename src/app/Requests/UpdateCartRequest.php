<?php

declare(strict_types=1);

namespace App\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'qty' => 'required|integer|min:1|max:99',
        ];
    }

    public function messages(): array
    {
        return [
            'qty.required' => 'Vui lòng nhập số lượng',
            'qty.integer' => 'Số lượng phải là số nguyên',
            'qty.min' => 'Số lượng tối thiểu là 1',
            'qty.max' => 'Số lượng tối đa là 99',
        ];
    }
}