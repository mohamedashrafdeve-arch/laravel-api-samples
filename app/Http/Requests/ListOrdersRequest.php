<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Order::class);
    }

    public function rules(): array
    {
        return [
            'search'    => ['nullable', 'string', 'max:100'],
            'status'    => ['nullable', Rule::in(Order::STATUSES)],
            'sort'      => ['nullable', Rule::in(['created_at', 'total'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page'  => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
