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
 * @method static \Illuminate\Database\Eloquent\Builder|CallistoAndArtio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CallistoAndArtio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CallistoAndArtio query()
 * @method static \Illuminate\Database\Eloquent\Builder|CallistoAndArtio whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CallistoAndArtio whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CallistoAndArtio whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CallistoAndArtio whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CallistoAndArtio whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CallistoAndArtio whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CallistoAndArtio extends Model
{
    protected $table = 'callisto_and_artio';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}