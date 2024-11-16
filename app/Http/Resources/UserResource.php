<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            //            'email' => $this->email,
            //            'email_verified_at' => $this->email_verified_at,
            //            'password' => $this->password,
            'icon_id' => $this->icon_id,
            'icon' => $this->icon,
            'private' => $this->private,
            'current_team_id' => $this->current_team_id,
            //            'two_factor_secret' => $this->two_factor_secret,
            //            'two_factor_recovery_codes' => $this->two_factor_recovery_codes,
            //            'two_factor_confirmed_at' => $this->two_factor_confirmed_at,
            //            'remember_token' => $this->remember_token,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
