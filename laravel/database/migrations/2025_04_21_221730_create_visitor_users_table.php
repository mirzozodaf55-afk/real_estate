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
        Schema::create('visitor_users', function (Blueprint $table) {
            $table->id();
            $table->uuid('visitor_id')->nullable();
            $table->ipAddress('ip-adress')->nullable();
            $table->text('user-agent')->nullable();
            $table->string('path')->nullable();
            $table->string('referer')->nullable();
            $table->string('fullUrl')->nullable();
            $table->timestamp('visited_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_users');
    }
};
