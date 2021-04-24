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
    public function skills($accountUsername)
    {
        return new AccountSkillResource(Helper::getAccountFromUsername($accountUsername));
    }

    public function skill($accountUsername, $skillName)
    {
        $skill = Skill::where('name', $skillName)->firstOrFail();

        return new SkillResource($skill->model::where('account_id', Helper::getAccountIdFromUsername($accountUsername))->first());
    }

    public function update($accountUsername, $skillName, Request $request)
    {
        if ($request->level > 99) {
            return response("Not funny", 406);
        }

        $account = Helper::checkIfUserOwnsAccount($accountUsername);

        $skill = Skill::where('name', $skillName)->firstOrFail();

        $skill = $skill->model::where('account_id', $account->id)->first();

        $skill->update(['level' => $request->level]);

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
            "icon" => strtolower($skillName),
            "message" => $accountUsername . " just achieved level " . $skill->level . " " . ucfirst($skillName) . "!",
        ];

        $event = Broadcast::create($eventData);

        EventAll::dispatch($event);

        AccountEvent::dispatch($account, $event);

        if ($skill->level == 99) {
            $announcementData = [
                "log_id" => $log->id,
                "type" => 'announcement',
                "icon" => strtolower($skillName),
                "message" => $accountUsername . " has achieved level 99 " . ucfirst($skillName) . "!",
            ];

            $announcement = Broadcast::create($announcementData);

            AnnouncementAll::dispatch($announcement);
        }

        return response("Advanced " . ucfirst($skillName) . " level for " . $accountUsername . " to level " . $request->level, 200);
    }
}
