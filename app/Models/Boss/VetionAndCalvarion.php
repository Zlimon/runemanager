<?php

namespace App\Models\Boss;

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
 * @method static \Illuminate\Database\Eloquent\Builder|VetionAndCalvarion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VetionAndCalvarion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VetionAndCalvarion query()
 * @method static \Illuminate\Database\Eloquent\Builder|VetionAndCalvarion whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VetionAndCalvarion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VetionAndCalvarion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VetionAndCalvarion whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VetionAndCalvarion whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VetionAndCalvarion whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class VetionAndCalvarion extends Model
{
    protected $table = 'vetion_and_calvarion';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}