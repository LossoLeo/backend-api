<?php
// filepath: app/Http/Requests/StoreFavoriteRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Rules\ProductNotAlreadyFavorited;

class StoreFavoriteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                'min:1',
                new ProductNotAlreadyFavorited(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => trans('favorites.product_id_required', [], 'pt_BR'),
            'product_id.integer' => trans('favorites.product_id_integer', [], 'pt_BR'),
            'product_id.min' => trans('favorites.product_id_min', [], 'pt_BR'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
