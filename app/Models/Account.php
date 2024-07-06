<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Account extends Model
{
    use HasFactory;

    public function getRouteKeyName(): string
    {
        return 'username';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

//    public function log() {
//        return $this->hasMany(Log::class);
//    }
//
//    public function logBroadcast() {
//        return $this->hasManyThrough(Broadcast::class, Log::class);
//    }
//
    public function collection(Collection $collection): HasOne
    {
        return $this->hasOne($collection->model);
    }
//
    public function skill(Skill $skill): HasOne
    {
        return $this->hasOne($skill->model);
    }
//
//    public function equipment() {
//        return $this->hasOne(Equipment::class);
//    }
//
//    public function bank() {
//        return $this->hasOne(Bank::class);
//    }
//
//    public function quest() {
//        return $this->hasOne(Quest::class);
//    }
//
//    public function group() {
//        return $this->belongsToMany(Group::class);
//    }
}
