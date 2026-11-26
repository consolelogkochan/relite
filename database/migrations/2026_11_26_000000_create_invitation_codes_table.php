<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invitation_codes', function (Blueprint $table) {
            $table->id();
            // 12文字の小文字の英字と数字（App\Models\InvitationCode が作る）
            $table->string('code', 12)->unique();
            // 使用済みかは、この列だけで決める（user_id は利用者が消えると null に戻るため）
            $table->timestamp('used_at')->nullable();
            // 使った利用者。利用者が消えたら null に戻す
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitation_codes');
    }
};
