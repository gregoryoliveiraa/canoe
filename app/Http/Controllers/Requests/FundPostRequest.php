<?php

namespace App\Http\Controllers\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FundPostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'fund'    => 'required|string|max:255',
            'manager' => 'required|string|max:255',
            'year'    => 'required|numeric',
            'alias'   => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'fund.required'    => 'Fund name is required',
            'manager.required' => 'Manager name is required',
            'year.required'    => 'Year is required',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'error'   => $validator->errors(),
            ])
        );
    }
}
