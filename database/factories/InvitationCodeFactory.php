<?php

namespace Database\Factories;

use App\Models\InvitationCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvitationCode>
 */
class InvitationCodeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => InvitationCode::newUniqueCode(),
        ];
    }

    /**
     * 使用済み
     */
    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'used_at' => now(),
            'user_id' => User::factory(),
        ]);
    }
}
