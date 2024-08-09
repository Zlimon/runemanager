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
 * @method static \Illuminate\Database\Eloquent\Builder|HallowedSepulchre newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HallowedSepulchre newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HallowedSepulchre query()
 * @method static \Illuminate\Database\Eloquent\Builder|HallowedSepulchre whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HallowedSepulchre whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HallowedSepulchre whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HallowedSepulchre whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HallowedSepulchre whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HallowedSepulchre whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class HallowedSepulchre extends Model
{
    protected $table = 'hallowed_sepulchre';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}