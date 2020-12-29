<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\AccountAuthStatus
 *
 * @property int $id
 * @property int $user_id
 * @property string $account_type
 * @property string $username
 * @property string $code
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|AccountAuthStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccountAuthStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccountAuthStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|AccountAuthStatus whereAccountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountAuthStatus whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountAuthStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountAuthStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountAuthStatus whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountAuthStatus whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountAuthStatus whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountAuthStatus whereUsername($value)
 * @mixin \Eloquent
 */
class AccountAuthStatus extends Model
{
    public function user() {
        return $this->belongsTo(User::class);
    }
}
