<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\NewsPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Features;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_accounts_can_be_deleted(): void
    {
        if (! Features::hasAccountDeletionFeatures()) {
            $this->markTestSkipped('Account deletion is not enabled.');
        }

        $this->actingAs($user = User::factory()->create());

        $this->delete('/user', [
            'password' => 'password',
        ]);

        $this->assertNull($user->fresh());
    }

    public function test_a_claimed_osrs_account_survives_its_owner_and_returns_to_the_roster(): void
    {
        if (! Features::hasAccountDeletionFeatures()) {
            $this->markTestSkipped('Account deletion is not enabled.');
        }

        $this->actingAs($user = User::factory()->create());
        $account = Account::factory()->for($user)->create(['username' => 'Claimed']);

        $this->delete('/user', [
            'password' => 'password',
        ])->assertSessionHasNoErrors();

        $this->assertNull($user->fresh());
        $this->assertNotNull($account->fresh());
        $this->assertNull($account->fresh()->user_id);
    }

    public function test_a_news_post_survives_its_author(): void
    {
        if (! Features::hasAccountDeletionFeatures()) {
            $this->markTestSkipped('Account deletion is not enabled.');
        }

        $this->actingAs($user = User::factory()->create());
        $post = NewsPost::factory()->create(['user_id' => $user->id]);

        $this->delete('/user', [
            'password' => 'password',
        ])->assertSessionHasNoErrors();

        $this->assertNull($user->fresh());
        $this->assertNotNull($post->fresh());
        $this->assertNull($post->fresh()->user_id);
    }

    public function test_correct_password_must_be_provided_before_account_can_be_deleted(): void
    {
        if (! Features::hasAccountDeletionFeatures()) {
            $this->markTestSkipped('Account deletion is not enabled.');
        }

        $this->actingAs($user = User::factory()->create());

        $this->delete('/user', [
            'password' => 'wrong-password',
        ]);

        $this->assertNotNull($user->fresh());
    }
}
