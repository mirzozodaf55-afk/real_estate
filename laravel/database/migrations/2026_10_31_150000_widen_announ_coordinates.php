<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // double(8,2) хранил координаты с точностью до 0.01° (~1 км): точка, выбранная на карте, «съезжала» на соседний квартал.
    // 6 знаков после запятой — точность до ~10 см. Сырой SQL: change() без doctrine/dbal в этом проекте недоступен.
    public function up(): void
    {
        DB::statement('ALTER TABLE announs MODIFY coord_x DECIMAL(10,6) NULL, MODIFY coord_y DECIMAL(10,6) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE announs MODIFY coord_x DOUBLE(8,2) NULL, MODIFY coord_y DOUBLE(8,2) NULL');
    }
};
