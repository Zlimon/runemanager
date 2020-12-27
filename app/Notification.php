<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
	protected $fillable = [
		'user_id', 'account_id', 'category_id', 'icon', 'message', 'data'
	];

	protected $casts = [
		'data' => 'array'
	];

	public function user() {
		return $this->belongsTo(User::class);
	}

	public function account() {
		return $this->belongsTo(Account::class);
	}

	public function category() {
		return $this->belongsTo(NotificationCategory::class);
	}
}
