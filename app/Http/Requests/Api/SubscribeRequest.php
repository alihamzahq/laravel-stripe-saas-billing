<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
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
            'plan_id' => ['required', 'exists:plans,id'],
            'payment_method' => ['required', 'in:card,bank_transfer'],
            'payment_method_id' => ['required_if:payment_method,card', 'nullable', 'string'],
            'billing_period' => ['nullable', 'in:monthly,yearly'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'plan_id.required' => 'Please select a plan.',
            'plan_id.exists' => 'The selected plan does not exist.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Invalid payment method. Choose either card or bank_transfer.',
            'payment_method_id.required_if' => 'Payment method ID is required for card payments.',
            'billing_period.in' => 'Invalid billing period. Choose either monthly or yearly.',
        ];
    }
}
