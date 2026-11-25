<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // モデルの creating のイベントで共有ID を作るので、WithoutModelEvents は使わない
        User::factory()->create([
            'name' => 'Test User',
            'login_id' => 'testuser',
        ]);
    }
}
