<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Broadcast;
use App\Events\AccountAll;
use App\Events\AccountEvent;
use App\Events\AccountLevelUp;

use App\Events\AnnouncementAll;
use App\Events\EventAll;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AccountSkillResource;
use App\Http\Resources\SkillResource;
use App\Log;

use App\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountSkillController extends Controller
{
    public function index(Account $account)
    {
        return new AccountSkillResource(Helper::getAccountFromUsername($account->username));
    }

    public function show(Account $account, Skill $skill)
    {
        return new SkillResource($skill->model::where('account_id', Helper::getAccountIdFromUsername($account->username))->first());
    }

    public function update(Account $account, Skill $skill, Request $request)
    {
        if ($request->level > 99) {
            return response("Not funny", 406);
        }

        $accountSkill = $skill->model::where('account_id', $account->id)->first();

        $accountSkill->update(['level' => $request->level]);

        $logData = [
            "user_id" => auth()->user()->id,
            "account_id" => $account->id,
            "category_id" => 1,
            "action" => $request->route()->getName(),
            "data" => $request->all()
        ];

        $log = Log::create($logData);

        $eventData = [
            "log_id" => $log->id,
            "type" => 'event',
            "icon" => strtolower($skill->name),
            "message" => $account->username . " just achieved level " . $accountSkill->level . " " . ucfirst($skill->name) . "!",
        ];

        $event = Broadcast::create($eventData);

        EventAll::dispatch($event);

        AccountEvent::dispatch($account, $event);

        if ($accountSkill->level == 99) {
            $announcementData = [
                "log_id" => $log->id,
                "type" => 'announcement',
                "icon" => strtolower($skill->name),
                "message" => $account->username . " has achieved level 99 " . ucfirst($skill->name) . "!",
            ];

            $announcement = Broadcast::create($announcementData);

            AnnouncementAll::dispatch($announcement);
        }

        return response("Advanced " . ucfirst($skill->name) . " level for " . $account->username . " to level " . $accountSkill->level, 200);
    }
}
