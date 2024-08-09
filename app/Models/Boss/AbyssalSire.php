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
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire query()
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AbyssalSire extends Model
{
    protected $table = 'abyssal_sire';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}