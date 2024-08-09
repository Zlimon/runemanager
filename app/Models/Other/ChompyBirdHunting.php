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
 * @method static \Illuminate\Database\Eloquent\Builder|ChompyBirdHunting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChompyBirdHunting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChompyBirdHunting query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChompyBirdHunting whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChompyBirdHunting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChompyBirdHunting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChompyBirdHunting whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChompyBirdHunting whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChompyBirdHunting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChompyBirdHunting extends Model
{
    protected $table = 'chompy_bird_hunting';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}