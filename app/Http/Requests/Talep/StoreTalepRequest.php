<?php

namespace App\Http\Requests\Talep;

use App\Models\Talep;
use Illuminate\Foundation\Http\FormRequest;

class StoreTalepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('create', Talep::class);
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
