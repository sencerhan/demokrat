<?php

namespace App\Http\Requests\Talep;

use App\Enums\TalepStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTalepStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('updateStatus', $this->route('talep')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(TalepStatus::class)],
        ];
    }
}
