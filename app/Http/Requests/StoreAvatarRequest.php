<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAvatarRequest extends FormRequest
{
    /**
     * Auth is enforced upstream by sanctum + the plugin.account middleware,
     * which also resolves the target Account onto the request attributes.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Wavefront OBJ + optional MTL exported by the RuneLite plugin.
     * OBJ/MTL are plain text so they're validated by extension rather than MIME.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'model' => ['required', 'file', 'extensions:obj', 'max:16384'],
            'material' => ['nullable', 'file', 'extensions:mtl', 'max:4096'],
            // The opponent's model, sent only while the player is fighting an NPC.
            'npc_model' => ['nullable', 'file', 'extensions:obj', 'max:65536'],
            'npc_material' => ['nullable', 'file', 'extensions:mtl', 'max:8192'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'model.extensions' => 'The avatar model must be a Wavefront .obj file.',
            'material.extensions' => 'The avatar material must be a .mtl file.',
            'npc_model.extensions' => 'The opponent model must be a Wavefront .obj file.',
            'npc_material.extensions' => 'The opponent material must be a .mtl file.',
        ];
    }
}
