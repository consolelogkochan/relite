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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // ログインに使う値。人に教えない。重なりは照合順序（接続の既定の utf8mb4_unicode_ci。
            // 大文字と小文字を区別しない）の一意の制約で断る。入力の決まり（3-2）でも小文字にそろえる
            $table->string('login_id', 30)->unique();
            // 人に教える値。アプリが作り（App\Models\User）、作り直せる
            $table->string('share_id', 12)->unique();
            $table->string('password');
            $table->boolean('is_admin')->default(false);
            // 空なら本物の利用者、値があればゲストで、その日時に消える（7-1 で期限の過ぎたゲストを探す）
            $table->timestamp('guest_expires_at')->nullable()->index();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
