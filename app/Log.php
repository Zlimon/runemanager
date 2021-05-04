<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Log
 *
 * @property int $id
 * @property int $user_id
 * @property int $account_id
 * @property int $category_id
 * @property string $action
 * @property array|null $data
 * @property int|null $total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @property-read \App\Category $category
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Log newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log query()
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereUserId($value)
 * @mixin \Eloquent
 */
class Log extends Model
{
    protected $fillable = [
        'user_id',
        'account_id',
        'category_id',
        'action',
        'data',
        'total'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
