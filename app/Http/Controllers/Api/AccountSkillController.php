<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Account;
use App\Notification;
use App\Events\All;
use App\Events\AccountLevelUp;

class AccountSkillController extends Controller
{
	public function update($accountUsername, $skillName, Request $request) {
		$account = Account::where('user_id', auth()->user()->id)->where('username', $accountUsername)->first();

		if ($account) {
			DB::table($skillName)->where('account_id', $account->id)->increment('level', 1/*, ["xp" => $request->xp]*/);

			$skill = DB::table($skillName)->where('account_id', $account->id)->first();

			$notificationData = [
				"user_id" => auth()->user()->id,
				"account_id" => $account->id,
				"category_id" => 1,
				"icon" => $skillName,
				"message" => $accountUsername." just achieved level ".$skill->level." ".ucfirst($skillName)."!".($skill->level === 92 ? " Half way there!" : ""),
				"data" => null
			];

			$notification = Notification::create($notificationData);

			All::dispatch($notification);

			AccountLevelUp::dispatch($account, $notification);

			return response()->json($skill, 200);
		} else {
			return response("This account is not authenticated with " . auth()->user()->name, 401);
		}
	}
}
