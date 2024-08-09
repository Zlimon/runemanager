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
 * @method static \Illuminate\Database\Eloquent\Builder|AllPets newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AllPets newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AllPets query()
 * @method static \Illuminate\Database\Eloquent\Builder|AllPets whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllPets whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllPets whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllPets whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllPets whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllPets whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AllPets extends Model
{
    protected $table = 'all_pets';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}