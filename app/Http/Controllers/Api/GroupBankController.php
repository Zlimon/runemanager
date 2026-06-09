<?php

namespace App\Http\Controllers\Api;

use App\Events\GroupBankUpdated;
use App\Http\Controllers\Controller;
use App\Models\GroupBank;
use App\Support\Instance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SPEC §5.2 — any GROUP-mode member's plugin pushes the full shared group bank
 * whenever it changes. Stored as a single overwritten document and broadcast so
 * the shared Group Bank page updates live. No-op outside GROUP mode.
 *
 * The Account is resolved by the plugin.account middleware.
 */
class GroupBankController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'group_bank' => ['present', 'array'],
            'group_bank.*' => ['array', 'size:2'],
            'group_bank.*.*' => ['integer'],
        ]);

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
