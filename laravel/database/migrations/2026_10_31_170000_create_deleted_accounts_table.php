<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Удалённые аккаунты: месяц нельзя завести новый на ту же почту. Храним не адрес, а его sha256 —
        // после удаления личных данных пользователя у нас не остаётся
        Schema::create('deleted_accounts', function (Blueprint $table) {
            $table->id();
            $table->char('email_hash', 64)->index();
            $table->timestamp('blocked_until');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deleted_accounts');
    }
};
