<?php

namespace App\Http\Requests\Talep;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTalepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('talep')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }
}
