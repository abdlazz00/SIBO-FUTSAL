<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cancel_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }
}
