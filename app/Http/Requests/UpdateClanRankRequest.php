<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** The player's own clan rank and title pushed by the RuneLite plugin. */
class UpdateClanRankRequest extends FormRequest
{
    /** Access is enforced by the route middleware. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'clan_rank' => ['nullable', 'integer', 'between:-128,127'],
            'clan_title' => ['nullable', 'string', 'max:60'],
        ];
    }
}
