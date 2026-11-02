<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Роли вместо одного флага is_admin: user / moderator / admin
        Schema::table('owners', function (Blueprint $table) {
            $table->string('role', 20)->default('user')->after('is_admin')->index();
        });
        DB::table('owners')->whereIn('is_admin', ['1', 'true'])->update(['role' => 'admin']);

        // Статус модерации. Новые объявления — на проверке, уже опубликованные считаем одобренными
        Schema::table('announs', function (Blueprint $table) {
            $table->string('moderation_status', 20)->default('pending')->after('owner_id')->index();
            $table->text('moderation_reason')->nullable()->after('moderation_status');
            $table->timestamp('moderated_at')->nullable()->after('moderation_reason');
            $table->unsignedBigInteger('moderated_by')->nullable()->after('moderated_at');
        });
        DB::table('announs')->update(['moderation_status' => 'approved']);

        // В колокольчике теперь не только «новое по подписке» (match), но и решение модератора для владельца
        Schema::table('announ_notifications', function (Blueprint $table) {
            $table->string('kind', 20)->default('match')->after('notify_me_id');
            $table->text('message')->nullable()->after('kind');
            $table->dropUnique(['user_id', 'announ_id']);
            $table->unique(['user_id', 'announ_id', 'kind']);
        });

        // Журнал: кто, когда и что сделал в админке
        Schema::create('moderation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('moderator_id')->index();
            $table->string('action', 30);
            $table->unsignedBigInteger('announ_id')->nullable()->index();
            $table->unsignedBigInteger('target_user_id')->nullable()->index();
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_logs');
        Schema::table('announ_notifications', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'announ_id', 'kind']);
            $table->unique(['user_id', 'announ_id']);
            $table->dropColumn(['kind', 'message']);
        });
        Schema::table('announs', function (Blueprint $table) {
            $table->dropColumn(['moderation_status', 'moderation_reason', 'moderated_at', 'moderated_by']);
        });
        Schema::table('owners', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
