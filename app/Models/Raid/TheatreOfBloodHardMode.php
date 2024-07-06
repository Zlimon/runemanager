<?php

namespace App\Models\Raid;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Raid\TheatreOfBloodHardMode
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBloodHardMode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBloodHardMode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBloodHardMode query()
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBloodHardMode whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBloodHardMode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBloodHardMode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBloodHardMode whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBloodHardMode whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBloodHardMode whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TheatreOfBloodHardMode extends Model
{
    protected $table = 'theatre_of_blood_hard_mode';

    protected $fillable = [
        'kill_count',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
