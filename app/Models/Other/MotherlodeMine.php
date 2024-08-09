<?php

namespace App\Models\Other;

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
 * @method static \Illuminate\Database\Eloquent\Builder|MotherlodeMine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MotherlodeMine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MotherlodeMine query()
 * @method static \Illuminate\Database\Eloquent\Builder|MotherlodeMine whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MotherlodeMine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MotherlodeMine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MotherlodeMine whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MotherlodeMine whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MotherlodeMine whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MotherlodeMine extends Model
{
    protected $table = 'motherlode_mine';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}