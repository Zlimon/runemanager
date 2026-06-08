<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvatarController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\HeartbeatController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\LootController;
use App\Http\Controllers\Api\LootingBagController;
use App\Http\Controllers\Api\PositionController;
use App\Http\Controllers\Api\QuestController;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\Api\VitalsController;
use App\Http\Controllers\UserResourcePackController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware([
    'auth:sanctum',
])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    /*
     * Plugin push endpoints. The OSRS account is resolved by the plugin.account
     * middleware from the X-Account-Hash + X-Account-Username headers, not from
     * the URL — usernames change but the hash is stable.
     */
    Route::middleware('plugin.account')->prefix('/plugin')->group(function () {
        Route::put('/inventory', [InventoryController::class, 'update'])->name('api.plugin.inventory');
        Route::put('/bank', [BankController::class, 'update'])->name('api.plugin.bank');
        Route::put('/equipment', [EquipmentController::class, 'update'])->name('api.plugin.equipment');
        Route::put('/quests', [QuestController::class, 'update'])->name('api.plugin.quests');
        Route::put('/looting-bag', [LootingBagController::class, 'update'])->name('api.plugin.looting-bag');
        // Loot is append-only — POST, not the snapshot PUT used by the others.
        Route::post('/loot', [LootController::class, 'store'])->name('api.plugin.loot');
        // Player 3D model from the RuneLite plugin (multipart OBJ + MTL).
        Route::post('/avatar', [AvatarController::class, 'update'])->name('api.plugin.avatar');
        // Presence ping — stamps last_seen_at; "online" is derived from it.
        Route::put('/heartbeat', [HeartbeatController::class, 'update'])->name('api.plugin.heartbeat');
        // Live Map position push — latest WorldPoint while sharing is enabled.
        Route::put('/position', [PositionController::class, 'update'])->name('api.plugin.position');
        // Live status-orb values (HP/prayer/run/special) for the Account Show orbs.
        Route::put('/vitals', [VitalsController::class, 'update'])->name('api.plugin.vitals');
        // Current in-game activity (Discord-plugin style) for the account cards/header.
        Route::put('/status', [StatusController::class, 'update'])->name('api.plugin.status');
        // In-game announcements (SPEC §9.2): pull unacknowledged, then ack each.
        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('api.plugin.announcements');
        Route::put('/announcements/{announcement}/acknowledge', [AnnouncementController::class, 'acknowledge'])
            ->name('api.plugin.announcements.acknowledge');
    });

    // Resource pack is a user preference (not OSRS-account-scoped), so it
    // sits outside the plugin.account middleware. Plugin pushes a pack name;
    // backend resolves to an id and writes users.resource_pack_id.
    Route::put('/plugin/resource-pack', [UserResourcePackController::class, 'updateFromPlugin'])
        ->name('api.plugin.resource-pack');
});
