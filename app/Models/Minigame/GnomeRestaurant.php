<?php

namespace App\Models\Minigame;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $account_id
 * @property int $obtained
 * @property int $kill_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|GnomeRestaurant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GnomeRestaurant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GnomeRestaurant query()
 * @method static \Illuminate\Database\Eloquent\Builder|GnomeRestaurant whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GnomeRestaurant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GnomeRestaurant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GnomeRestaurant whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GnomeRestaurant whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GnomeRestaurant whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GnomeRestaurant extends Model
{
    protected $table = 'gnome_restaurant';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}