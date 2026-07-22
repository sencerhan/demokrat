<?php

namespace App\Http\Requests\Talep;

use App\Enums\VoteDirection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTalepVoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('vote', $this->route('talep')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'direction' => ['required', Rule::enum(VoteDirection::class)],
        ];
    }
}
