<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuickInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
            'related_package_id' => ['nullable', 'integer', 'exists:packages,id'],
            'website' => ['prohibited'],
        ];
    }
}
