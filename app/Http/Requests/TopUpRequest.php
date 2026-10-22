<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TopUpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_active;
    }

    /**
     * Get the validation rules that apply to the request.
     * Strict defense against negative balance, float truncation, or overflow tampering.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'integer',
                'min:10000',      // Minimum top up Rp 10.000
                'max:10000000',   // Maximum top up Rp 10.000.000
            ],
        ];
    }

    /**
     * Custom error messages in Indonesian.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Nominal top-up wajib diisi.',
            'amount.integer' => 'Nominal top-up harus berupa bilangan bulat tanpa desimal atau simbol mata uang.',
            'amount.min' => 'Batas minimum top-up adalah Rp 10.000.',
            'amount.max' => 'Batas maksimum top-up per transaksi adalah Rp 10.000.000.',
        ];
    }
}

