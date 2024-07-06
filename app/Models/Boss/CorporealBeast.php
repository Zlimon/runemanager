<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\CorporealBeast
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $pet_dark_core
 * @property int $elysian_sigil
 * @property int $spectral_sigil
 * @property int $arcane_sigil
 * @property int $holy_elixir
 * @property int $spirit_shield
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast query()
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast whereArcaneSigil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast whereElysianSigil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast whereHolyElixir($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast wherePetDarkCore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast whereSpectralSigil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast whereSpiritShield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CorporealBeast extends Model
{
    protected $table = 'corporeal_beast';

    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_dark_core',
        'elysian_sigil',
        'spectral_sigil',
        'arcane_sigil',
        'holy_elixir',
        'spirit_shield',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
