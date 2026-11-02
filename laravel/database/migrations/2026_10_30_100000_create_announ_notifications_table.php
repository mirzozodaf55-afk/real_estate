<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Уведомления «по вашей подписке появилось объявление».
     * Раньше они только транслировались по websocket и терялись, если пользователь был не в сети.
     */
    public function up(): void
    {
        Schema::create('announ_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('announ_id');
            $table->unsignedBigInteger('notify_me_id')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // одно и то же объявление не уведомляем дважды, даже если совпало с несколькими подписками
            $table->unique(['user_id', 'announ_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announ_notifications');
    }
};
