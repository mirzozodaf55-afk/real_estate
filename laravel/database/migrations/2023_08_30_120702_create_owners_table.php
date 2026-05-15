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
        Schema::create('owners', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('avatar')->default('avatar.jpg');
            $table->string('birth_date')->nullable();
            $table->string('email')->nullable();
            $table->string('pass')->nullable();
            $table->integer('verification_code')->nullable();
            $table->string('phone_number');
            $table->string('ip_adress')->nullable();
            $table->string('is_admin')->default(false);
            $table->string('uniq_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owners');
    }
};
