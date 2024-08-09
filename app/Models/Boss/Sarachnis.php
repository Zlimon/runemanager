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
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis query()
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Sarachnis extends Model
{
    protected $table = 'sarachnis';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}