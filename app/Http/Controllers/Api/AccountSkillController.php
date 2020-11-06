<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Account;
use App\Events\AccountLevelUp;

class AccountSkillController extends Controller
{
	public function update($accountUsername, $skillName, Request $request) {
		$account = Account::where('user_id', auth()->user()->id)->where('username', $accountUsername)->first();

		DB::table($skillName)->where('account_id', $account->id)->increment('level', 1/*, ["xp" => $request->xp]*/);

		$skill = DB::table($skillName)->where('account_id', $account->id)->first();

		$notificationData = [
			"category" => "skill",
			"name" => $skillName,
			"loot" => [],
			"message" => $accountUsername." just achieved level ".$skill->level." ".ucfirst($skillName)."!".($skill->level === 92 ? " Half way there :)" : "")
		];

		AccountLevelUp::dispatch($notificationData);

		return response()->json($skill, 200);
	}
}
