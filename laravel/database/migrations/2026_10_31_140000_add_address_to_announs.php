<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Адрес, который владелец выбрал на карте при подаче (раньше сохранялись только координаты —
        // поэтому поиск по городу и улице ничего не находил)
        Schema::table('announs', function (Blueprint $table) {
            $table->string('address', 255)->nullable()->after('coord_y');
        });
    }

    public function down(): void
    {
        Schema::table('announs', fn (Blueprint $table) => $table->dropColumn('address'));
    }
};
