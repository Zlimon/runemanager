<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Quest
 *
 * @property int $id
 * @property int $account_id
 * @property array|null $data
 * @property int $display
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Quest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Quest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Quest query()
 * @method static \Illuminate\Database\Eloquent\Builder|Quest whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quest whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quest whereDisplay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Quest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function account() {
        return $this->belongsTo(Account::class);
    }
}
