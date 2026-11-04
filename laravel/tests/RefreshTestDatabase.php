<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * RefreshDatabase, который отказывается работать с не тестовой базой.
 * Он стирает все таблицы: если конфиг закеширован (php artisan config:cache) или из phpunit.xml
 * пропал DB_DATABASE, тесты стёрли бы рабочую базу. Используйте вместо RefreshDatabase.
 */
trait RefreshTestDatabase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase()
    {
        $database = (string) config('database.connections.' . config('database.default') . '.database');
        if (!str_ends_with($database, '_test')) {
            throw new \RuntimeException("Тесты запущены не на тестовой базе ($database). Нужна база *_test — см. phpunit.xml.");
        }
    }
}
