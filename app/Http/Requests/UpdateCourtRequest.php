<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:indoor,outdoor'],
            'price' => ['required', 'numeric', 'min:0'],
            'slot_duration' => ['required', 'integer', 'min:15', 'max:240'],
            'open_time' => ['required', 'string'],
            'close_time' => ['required', 'string'],
            'status' => ['required', 'string', 'in:active,inactive,maintenance'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }
}
