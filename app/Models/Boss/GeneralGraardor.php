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
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor query()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GeneralGraardor extends Model
{
    protected $table = 'general_graardor';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}