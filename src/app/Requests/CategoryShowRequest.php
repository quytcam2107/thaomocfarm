<?php

declare(strict_types=1);

namespace App\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validate query string trang danh mục: sắp xếp, khoảng giá, đánh giá, danh mục con, trang.
 */
class CategoryShowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc validate cho bộ lọc danh mục (GET query).
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'sort' => ['sometimes', 'string', Rule::in(['bestsell', 'newest', 'price_asc', 'price_desc'])],
            'price' => ['sometimes', 'array'],
            'price.*' => ['string', Rule::in(['0-100', '100-250', '250-'])],
            'rating' => ['sometimes', 'nullable', 'integer', 'in:3,4,5'],
            'cat' => ['sometimes', 'array'],
            'cat.*' => ['integer', 'exists:categories,id'],
            'page' => ['sometimes', 'integer', 'min:1', 'max:10000'],
        ];
    }

    /**
     * Thông báo lỗi tiếng Việt.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sort.in' => 'Thứ tự sắp xếp không hợp lệ.',
            'price.array' => 'Khoảng giá không hợp lệ.',
            'price.*.in' => 'Khoảng giá không hợp lệ.',
            'rating.integer' => 'Mức đánh giá không hợp lệ.',
            'rating.in' => 'Mức đánh giá không hợp lệ.',
            'cat.*.integer' => 'Danh mục con không tồn tại.',
            'cat.*.exists' => 'Danh mục con không tồn tại.',
            'page.integer' => 'Số trang không hợp lệ.',
            'page.min' => 'Số trang không hợp lệ.',
            'page.max' => 'Số trang không hợp lệ.',
        ];
    }
}