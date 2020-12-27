<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Notification;
use App\NotificationCategory;
use App\Account;

use App\Http\Resources\AccountResource;

class NotificationController extends Controller
{
	public function index() {
		$notifications = Notification::with('category')->orderBy('id', 'DESC')->limit(5)->get();

		return response()->json($notifications, 200);
	}

	public function show($accountUsername) {
		$account = Account::where('username', $accountUsername)->pluck('user_id')->first();

		$notifications = Notification::with('category')->where('account_id', $account)->orderBy('id', 'DESC')->paginate(10);

		return response()->json($notifications, 200);
	}
}
