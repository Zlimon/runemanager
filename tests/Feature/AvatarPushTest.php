<?php

use App\Events\AccountDataUpdated;
use App\Models\Account;
use App\Models\User;
use App\Services\Accounts\DeleteAccount;
use App\Services\Accounts\MoveAvatarsToRandomDirectories;
use App\Services\Accounts\StoreAvatar;
use App\Services\Instance\ResetInstanceData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

function avatarUser(string $email = 'avatar@test.local'): User
{
    return User::query()->forceCreate([
        'name' => 'Avatar User',
        'email' => $email,
        'password' => Hash::make('pw'),
        'icon_id' => 0,
    ]);
}

function avatarHeaders(string $hash = 'hash-avatar', string $username = 'Zlimon'): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => $hash,
        'X-Account-Username' => $username,
    ];
}

it('stores the obj + mtl pair and stamps the account', function () {
    $user = avatarUser();
    Sanctum::actingAs($user);

    $response = $this->post('/api/plugin/avatar', [
        'model' => UploadedFile::fake()->createWithContent('player-808-0.obj', "o player\nv 0 0 0\n"),
        'material' => UploadedFile::fake()->createWithContent('player-808-0.mtl', "newmtl c0\nKd 1 0 0\n"),
    ], avatarHeaders())->assertSuccessful();

    $account = Account::firstOrFail();
    $directory = $account->avatar_directory;

    // A random folder, never one derived from the account.
    expect($directory)->toMatch('#^avatars/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$#');
    Storage::disk('public')->assertExists("{$directory}/avatar.obj");
    Storage::disk('public')->assertExists("{$directory}/avatar.mtl");
    Storage::disk('public')->assertMissing("avatars/{$account->id}");

    expect($account->avatar_uploaded_at)->not->toBeNull();
    $response->assertJsonPath('data.avatar_uploaded_at', $account->avatar_uploaded_at->toIso8601String());
});

it('broadcasts AccountDataUpdated(avatar) so an open profile reloads the model', function () {
    Event::fake([AccountDataUpdated::class]);
    Sanctum::actingAs(avatarUser());

    $this->post('/api/plugin/avatar', [
        'model' => UploadedFile::fake()->createWithContent('player-808-0.obj', "o player\nv 0 0 0\n"),
    ], avatarHeaders())->assertSuccessful();

    $account = Account::firstOrFail();
    Event::assertDispatched(
        AccountDataUpdated::class,
        fn (AccountDataUpdated $event): bool => $event->account->is($account) && $event->type === 'avatar',
    );
});

it('stores the opponent model when fighting and exposes it on the payload', function () {
    Sanctum::actingAs(avatarUser());

    $this->post('/api/plugin/avatar', [
        'model' => UploadedFile::fake()->createWithContent('player.obj', "v 0 0 0\n"),
        'material' => UploadedFile::fake()->createWithContent('player.mtl', "newmtl c0\n"),
        'npc_model' => UploadedFile::fake()->createWithContent('npc.obj', "v 1 0 0\n"),
        'npc_material' => UploadedFile::fake()->createWithContent('npc.mtl', "newmtl n0\n"),
    ], avatarHeaders())->assertSuccessful();

    $account = Account::firstOrFail();
    $directory = $account->avatar_directory;
    Storage::disk('public')->assertExists("{$directory}/avatar_npc.obj");
    Storage::disk('public')->assertExists("{$directory}/avatar_npc.mtl");

    $payload = $account->avatarPayload();
    expect($payload['npc_obj_url'])->toContain("{$directory}/avatar_npc.obj");
    expect($payload['npc_mtl_url'])->toContain("{$directory}/avatar_npc.mtl");
});

it('clears a stale opponent model when the next push has none', function () {
    Sanctum::actingAs(avatarUser());

    $this->post('/api/plugin/avatar', [
        'model' => UploadedFile::fake()->createWithContent('player.obj', "v 0 0 0\n"),
        'npc_model' => UploadedFile::fake()->createWithContent('npc.obj', "v 1 0 0\n"),
    ], avatarHeaders())->assertSuccessful();

    $account = Account::firstOrFail();
    $first = $account->avatar_directory;
    Storage::disk('public')->assertExists("{$first}/avatar_npc.obj");

    // Stopped fighting — next push omits the NPC.
    $this->post('/api/plugin/avatar', [
        'model' => UploadedFile::fake()->createWithContent('player.obj', "v 0 0 0\n"),
    ], avatarHeaders())->assertSuccessful();

    $second = $account->fresh()->avatar_directory;
    expect($second)->not->toBe($first);
    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertMissing("{$second}/avatar_npc.obj");
    expect($account->fresh()->avatarPayload()['npc_obj_url'])->toBeNull();
});

it('accepts a model with no material and clears any stale material', function () {
    $user = avatarUser();
    Sanctum::actingAs($user);

    // First push includes a material.
    $this->post('/api/plugin/avatar', [
        'model' => UploadedFile::fake()->createWithContent('a.obj', "v 0 0 0\n"),
        'material' => UploadedFile::fake()->createWithContent('a.mtl', "newmtl c0\n"),
    ], avatarHeaders())->assertSuccessful();

    $account = Account::firstOrFail();
    Storage::disk('public')->assertExists("{$account->avatar_directory}/avatar.mtl");

    // Second push drops the material — the stale one must be removed.
    $this->post('/api/plugin/avatar', [
        'model' => UploadedFile::fake()->createWithContent('b.obj', "v 1 1 1\n"),
    ], avatarHeaders())->assertSuccessful();

    $directory = $account->fresh()->avatar_directory;
    Storage::disk('public')->assertExists("{$directory}/avatar.obj");
    Storage::disk('public')->assertMissing("{$directory}/avatar.mtl");
    expect($account->fresh()->avatarPayload()['mtl_url'])->toBeNull();
});

it('rejects a model that is not an obj file', function () {
    Sanctum::actingAs(avatarUser());

    $this->post('/api/plugin/avatar', [
        'model' => UploadedFile::fake()->createWithContent('avatar.txt', 'nope'),
    ], avatarHeaders())
        ->assertStatus(422)
        ->assertJsonValidationErrors(['model']);
});

it('rejects a material that is not an mtl file', function () {
    Sanctum::actingAs(avatarUser());

    $this->post('/api/plugin/avatar', [
        'model' => UploadedFile::fake()->createWithContent('avatar.obj', "v 0 0 0\n"),
        'material' => UploadedFile::fake()->createWithContent('avatar.png', 'nope'),
    ], avatarHeaders())
        ->assertStatus(422)
        ->assertJsonValidationErrors(['material']);
});

it('rejects an upload with no model file', function () {
    Sanctum::actingAs(avatarUser());

    $this->post('/api/plugin/avatar', [], avatarHeaders())
        ->assertStatus(422)
        ->assertJsonValidationErrors(['model']);
});

it('requires authentication', function () {
    $this->post('/api/plugin/avatar', [
        'model' => UploadedFile::fake()->createWithContent('avatar.obj', "v 0 0 0\n"),
    ], ['Accept' => 'application/json'])->assertUnauthorized();
});

it('requires the account headers', function () {
    Sanctum::actingAs(avatarUser());

    $this->post('/api/plugin/avatar', [
        'model' => UploadedFile::fake()->createWithContent('avatar.obj', "v 0 0 0\n"),
    ], ['Accept' => 'application/json'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['X-Account-Hash', 'X-Account-Username']);
});

it('removes a legacy account-id folder and its stale opponent model on the next upload', function () {
    $account = Account::factory()->create();
    $legacy = "avatars/{$account->id}";
    Storage::disk('public')->put("{$legacy}/avatar.obj", "v 9 9 9\n");
    Storage::disk('public')->put("{$legacy}/avatar_npc.obj", "v 1 0 0\n");
    Storage::disk('public')->put("{$legacy}/avatar_npc.mtl", "newmtl n0\n");

    app(StoreAvatar::class)->store(
        $account,
        UploadedFile::fake()->createWithContent('player.obj', "v 0 0 0\n"),
        null,
        null,
        null,
    );

    $directory = $account->fresh()->avatar_directory;
    Storage::disk('public')->assertExists("{$directory}/avatar.obj");
    Storage::disk('public')->assertMissing("{$directory}/avatar_npc.obj");
    Storage::disk('public')->assertMissing($legacy);
    expect($account->fresh()->avatar_uploaded_at)->not->toBeNull();
});

it('stores the NPC model without a material when none arrives', function () {
    $account = Account::factory()->create();

    app(StoreAvatar::class)->store(
        $account,
        UploadedFile::fake()->createWithContent('player.obj', "v 0 0 0\n"),
        null,
        UploadedFile::fake()->createWithContent('npc.obj', "v 1 0 0\n"),
        null,
    );

    $directory = $account->fresh()->avatar_directory;
    Storage::disk('public')->assertExists("{$directory}/avatar_npc.obj");
    Storage::disk('public')->assertMissing("{$directory}/avatar_npc.mtl");
    expect($account->fresh()->avatarPayload()['npc_mtl_url'])->toBeNull();
});

it('has no avatar payload before the first upload', function () {
    expect(Account::factory()->create()->avatarPayload())->toBeNull();
});

it('moves legacy account-id folders into random ones, once', function () {
    $moved = Account::factory()->create(['avatar_uploaded_at' => now()]);
    $gone = Account::factory()->create(['avatar_uploaded_at' => now()]);
    Storage::disk('public')->put("avatars/{$moved->id}/avatar.obj", "v 0 0 0\n");
    Storage::disk('public')->put("avatars/{$moved->id}/avatar.mtl", "newmtl c0\n");

    expect(app(MoveAvatarsToRandomDirectories::class)->run())->toBe(['moved' => 1, 'missing' => 1]);

    $directory = $moved->fresh()->avatar_directory;
    expect($directory)->toStartWith('avatars/')->not->toBe("avatars/{$moved->id}");
    Storage::disk('public')->assertExists("{$directory}/avatar.obj");
    Storage::disk('public')->assertExists("{$directory}/avatar.mtl");
    Storage::disk('public')->assertMissing("avatars/{$moved->id}");
    expect($moved->fresh()->avatarPayload()['obj_url'])->toContain("{$directory}/avatar.obj");

    // Nothing on disk for it: it stops claiming an avatar rather than pointing at nothing.
    expect($gone->fresh()->avatar_uploaded_at)->toBeNull()
        ->and($gone->fresh()->avatarPayload())->toBeNull();

    // Safe to run again.
    expect(app(MoveAvatarsToRandomDirectories::class)->run())->toBe(['moved' => 0, 'missing' => 0])
        ->and($moved->fresh()->avatar_directory)->toBe($directory);
});

it('deletes the avatar when an account is removed or the instance is reset', function () {
    $removed = Account::factory()->create();
    $kept = Account::factory()->create();

    foreach ([$removed, $kept] as $account) {
        app(StoreAvatar::class)->store($account, UploadedFile::fake()->createWithContent('p.obj', "v 0 0 0\n"), null, null, null);
    }

    $removedDirectory = $removed->fresh()->avatar_directory;
    $keptDirectory = $kept->fresh()->avatar_directory;

    app(DeleteAccount::class)->run($removed->fresh());

    Storage::disk('public')->assertMissing($removedDirectory);
    Storage::disk('public')->assertExists("{$keptDirectory}/avatar.obj");

    app(ResetInstanceData::class)->run();

    Storage::disk('public')->assertMissing($keptDirectory);
});
