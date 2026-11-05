<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WebsiteContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['name' => 'required|string|max:150', 'phone' => ['required', 'string', 'max:40', 'regex:/^[+0-9 ()-]{7,40}$/'], 'email' => 'nullable|email|max:190', 'age' => 'nullable|integer|min:1|max:120', 'complaint' => 'nullable|string|max:1000', 'date' => 'nullable|date|after_or_equal:today', 'details' => 'nullable|string|max:5000', 'service' => 'nullable|string|max:190', 'preferred_time' => 'nullable|string|max:100'];
    }
}
