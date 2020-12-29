<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Http\Controllers\Controller;
use App\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::with('category')->orderByDesc('id')->limit(5)->get();

        return response()->json($notifications, 200);
    }

    public function show($accountUsername)
    {
        $account = Account::where('username', $accountUsername)->pluck('id')->first();

        $notifications = Notification::with('category')->where('account_id', $account)->orderByDesc('id')->paginate(10);

        return response()->json($notifications, 200);
    }
}
