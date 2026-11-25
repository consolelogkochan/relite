<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_rejects_a_login_id_that_differs_only_in_case(): void
    {
        User::factory()->create(['login_id' => 'taro']);

        $this->expectException(UniqueConstraintViolationException::class);

        User::factory()->create(['login_id' => 'Taro']);
    }

    public function test_database_rejects_the_same_login_id(): void
    {
        User::factory()->create(['login_id' => 'taro']);

        $this->expectException(UniqueConstraintViolationException::class);

        User::factory()->create(['login_id' => 'taro']);
    }

    public function test_share_id_is_made_of_twelve_lowercase_letters_and_digits(): void
    {
        $user = User::factory()->create();

        $this->assertMatchesRegularExpression('/\A[a-z0-9]{12}\z/', $user->share_id);
    }

    public function test_two_users_get_different_share_ids(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->assertNotSame($first->share_id, $second->share_id);
    }

    public function test_regenerating_changes_the_share_id(): void
    {
        $user = User::factory()->create();
        $before = $user->share_id;

        $user->regenerateShareId();

        $this->assertNotSame($before, $user->fresh()?->share_id);
    }

    public function test_database_rejects_the_same_share_id(): void
    {
        $first = User::factory()->create();

        $this->expectException(UniqueConstraintViolationException::class);

        User::factory()->create(['share_id' => $first->share_id]);
    }

    public function test_guest_state_makes_a_guest(): void
    {
        $this->assertTrue(User::factory()->guest()->create()->isGuest());
        $this->assertFalse(User::factory()->create()->isGuest());
    }

    public function test_a_new_user_is_not_an_admin(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->fresh()?->is_admin);
    }
}
