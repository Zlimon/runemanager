<?php

namespace App\Http\Controllers\Api;

use App\Events\GroupBankUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateGroupBankRequest;
use App\Models\GroupBank;
use App\Support\Instance;
use Illuminate\Http\JsonResponse;

/**
 * Any GROUP-mode member's plugin pushes the full shared group bank
 * whenever it changes. Stored as a single overwritten document and broadcast so
 * the shared Group Bank page updates live. No-op outside GROUP mode.
 *
 * The Account is resolved by the plugin.account middleware.
 */
class GroupBankController extends Controller
{
    public function update(UpdateGroupBankRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (! Instance::isGroup()) {
            return response()->json(['data' => ['stored' => false]]);
        }

        $bank = GroupBank::query()->first() ?? new GroupBank;
        $bank->items = $validated['group_bank'];
        $bank->save();

        broadcast(new GroupBankUpdated);

        return response()->json(['data' => ['stored' => true, 'slots' => count($validated['group_bank'])]]);
    }
}
