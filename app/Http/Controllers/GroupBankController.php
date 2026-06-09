<?php

namespace App\Http\Controllers;

use App\Http\Resources\GroupBankResource;
use App\Models\GroupBank;
use App\Support\Instance;
use Inertia\Inertia;
use Inertia\Response;

/**
 * SPEC §5.2 — the shared Group Ironman group bank view. GROUP mode only.
 */
class GroupBankController extends Controller
{
    public function index(): Response
    {
        abort_unless(Instance::isGroup(), 404);

        $bank = GroupBank::query()->first();

        return Inertia::render('GroupBank/Index', [
            'groupName' => Instance::name(),
            'groupBank' => $bank ? (new GroupBankResource($bank))->resolve() : null,
        ]);
    }
}
