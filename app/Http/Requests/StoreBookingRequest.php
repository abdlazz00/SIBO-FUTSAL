<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'court_id' => ['required', 'exists:courts,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'string'], // format H:i or H:i:s
            'end_time' => ['required', 'string'],
        ];
    }
}
