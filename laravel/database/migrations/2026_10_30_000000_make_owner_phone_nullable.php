<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Вход теперь по email-коду, телефон становится необязательным полем профиля.
     * Через сырой SQL, потому что ->change() в Laravel 10 требует doctrine/dbal.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE owners MODIFY phone_number VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE owners SET phone_number = '' WHERE phone_number IS NULL");
        DB::statement('ALTER TABLE owners MODIFY phone_number VARCHAR(255) NOT NULL');
    }
};
