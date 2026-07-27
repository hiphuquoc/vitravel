<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomTourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'adults' => ['required', 'integer', 'min:1', 'max:30'],
            'children' => ['nullable', 'integer', 'min:0', 'max:20'],
            'infants' => ['nullable', 'integer', 'min:0', 'max:10'],
            'duration_text' => ['required', 'string', 'max:100'],
            'arrival_date' => ['required', 'date', 'after_or_equal:today'],
            'countries' => ['required', 'array', 'min:1'],
            'countries.*' => ['string', 'max:100'],
            'accommodation' => ['required', 'array', 'min:1'],
            'accommodation.*' => ['string', 'max:120'],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
            'budget_unit' => ['nullable', 'string', 'in:Mỗi người,Cả nhóm,per_person,per_group'],
            'gender' => ['required', 'string', 'in:Ông,Bà,Mr,Ms'],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:40'],
            'nationality' => ['required', 'string', 'max:80'],
            'city' => ['required', 'string', 'max:120'],
            'additional_notes' => ['nullable', 'string', 'max:5000'],
            'website' => ['prohibited'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'children' => $this->input('children', 0),
            'infants' => $this->input('infants', 0),
        ]);
    }
}
