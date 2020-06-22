<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
	public static function findByName($name) {
		return self::where('name', $name)->first();
	}

	public function collection() {
		return $this->morphTo();
	}
}
