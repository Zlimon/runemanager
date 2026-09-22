<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[WithoutTimestamps]
class Skill extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('order');
    }
}
