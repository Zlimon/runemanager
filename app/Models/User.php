<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

/**
 *
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property mixed $password
 * @property int $icon_id
 * @property int $private
 * @property int|null $current_team_id
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Account> $account
 * @property-read int|null $account_count
 * @property-read \App\Models\Team|null $currentTeam
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $ownedTeams
 * @property-read int|null $owned_teams_count
 * @property-read string $profile_photo_url
 * @property-read \App\Models\Membership $membership
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $teams
 * @property-read int|null $teams_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCurrentTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIconId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePrivate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'icon_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function account(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function getIconAttribute(): string
    {
        $itemIcon = Item::where('_id', $this->icon_id)->first();

        return $itemIcon ? $itemIcon->icon : 'iVBORw0KGgoAAAANSUhEUgAAACQAAAAgCAYAAAB6kdqOAAACPElEQVR4Xs2XW0sCQRTHO0/RY/QQFUE99BBEVEQkRQQRUQkWEhWViYh0FxERu4iIFF3o+hAFic/hdzxxZpxmZ8Yu5uzaHwZ2dtY9v3OZ2WNTk2cC1O80QATBRzK5U7lumABLpSe8v8/jzXUW4/EoAxrq6XEDSnqur0gBA3l8LGChkMbDwwj6/bM//KZmcYhcLomp1B4zUh2MR0ek6fw8g9HoBu7ubNuMEmA2m2AvjMW2MBRaQZ9vlM3T6QMNCvD2NucwDri5GWSRsgp0ehLHyckxR1T4EKA6FBmWxvm6eq8uAR5njrSXAkudMNbS0qxA6YYtwpBkanSvBQjVlpk+V6VGh4wXiw8OSMB8PuU4czwAU0POo0NFTteUUifM8/OlN1BS3DDtHieIiJYodot18514zcjUyHQKIDp75LrrMiHMSABeXZ59nj/qmjWpO8uE0AV4cXHiFhT3mIr1/b3IdtTvjLgCJc+hRCLGhijkn6NEUlOsr9YoYF/tYHAROzraK17yMTI8+GuvLcGQACORdRwY6Ne8BJyenqghSlbEUzQ1NV4l5ICBwJz3QMvL81Vg+Bq1FR4BOQ+0ajD8vvhsmGtWBfj6cs2aMAH2tcGvYOuS3DU0qBFbW11izVhfX6/R5+jPW4ehZpwOLup9aU41Qzuqra1VMSp2VrlcwoWFGfaMCWtFurfq3Amzvxdmkevu6jSe0d9al1TD5lwYF2eRCeupeF2Fw6uNBhECfHu7s/2/ql65WC9/1b+C8Uof5wvhG5Ohd9UAAAAASUVORK5CYII=';
    }
}
