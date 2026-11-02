<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Платное продвижение: VIP — самый верх списка, ТОП — под VIP. Действует, пока promo_until в будущем
        Schema::table('announs', function (Blueprint $table) {
            $table->string('promo', 8)->nullable()->after('moderation_status');
            $table->timestamp('promo_until')->nullable()->after('promo');
            $table->index(['promo', 'promo_until']);
        });

        // Заявки владельцев: оплата пока вручную — владелец переводит по реквизитам, админ подтверждает
        Schema::create('promotion_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announ_id');
            $table->foreignId('owner_id');
            $table->string('plan', 8);               // top | vip
            $table->unsignedSmallInteger('days');
            $table->unsignedInteger('price');        // сомони — цена на момент заявки
            $table->string('payment_note', 300)->nullable(); // номер перевода / комментарий владельца
            $table->string('status', 12)->default('pending')->index(); // pending | approved | rejected
            $table->string('reason', 300)->nullable();
            $table->foreignId('decided_by')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->index(['announ_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_requests');
        Schema::table('announs', function (Blueprint $table) {
            $table->dropIndex(['promo', 'promo_until']);
            $table->dropColumn(['promo', 'promo_until']);
        });
    }
};
