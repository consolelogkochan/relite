<?php

namespace Database\Seeders;

use App\Models\InvitationCode;
use Illuminate\Database\Seeder;

class InvitationCodeSeeder extends Seeder
{
    /**
     * 手元とテストのために、まだ使われていない招待コードを5つ作る
     */
    public function run(): void
    {
        InvitationCode::factory()->count(5)->create();
    }
}
