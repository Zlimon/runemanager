<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\TzKalZuk
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $jal-nib-rek
 * @property int $infernal_cape
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|TzKalZuk newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TzKalZuk newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TzKalZuk query()
 * @method static \Illuminate\Database\Eloquent\Builder|TzKalZuk whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzKalZuk whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzKalZuk whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzKalZuk whereInfernalCape($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzKalZuk whereJalNibRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzKalZuk whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzKalZuk whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzKalZuk whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzKalZuk whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TzKalZuk extends Model
{
    protected $table = 'tzkal_zuk';

    protected $fillable = [
        'obtained',
        'kill_count',
        'jal-nib-rek',
        'infernal_cape',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
