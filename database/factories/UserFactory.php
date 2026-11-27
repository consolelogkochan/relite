<?php

namespace Database\Factories;

use App\Models\User;
use App\Support\RecoveryCode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * share_id は入れない（モデルが作る）。
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'login_id' => fake()->unique()->regexify('[a-z][a-z0-9]{7,14}'),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * 回復コードを持つ（そのままの値 $code のハッシュが入る）
     */
    public function withRecoveryCode(string $code): static
    {
        return $this->state(fn (array $attributes) => [
            'recovery_code_hash' => RecoveryCode::hash($code),
        ]);
    }

    /**
     * 管理者
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
        ]);
    }

    /**
     * ゲスト（ゲストの期限が入る）
     */
    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'guest_expires_at' => now()->addHours(2),
        ]);
    }
}
