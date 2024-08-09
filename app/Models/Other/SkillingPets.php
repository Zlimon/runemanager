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
 * @method static \Illuminate\Database\Eloquent\Builder|SkillingPets newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SkillingPets newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SkillingPets query()
 * @method static \Illuminate\Database\Eloquent\Builder|SkillingPets whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SkillingPets whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SkillingPets whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SkillingPets whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SkillingPets whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SkillingPets whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SkillingPets extends Model
{
    protected $table = 'skilling_pets';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}