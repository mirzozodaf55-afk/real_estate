<?php

namespace App\Console\Commands;

use App\Support\Demo;
use Database\Seeders\DemoSeeder;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Вернуть публичное демо к исходному состоянию: база с нуля + демо-данные (DemoSeeder).
 * Стирает ВСЮ базу, поэтому работает только при DEMO_MODE=true.
 * Запускается при старте контейнера (docker/entrypoint.sh) и каждую ночь (app/Console/Kernel.php).
 */
class DemoReset extends Command
{
    protected $signature = 'demo:reset {--clean-files : удалить и загруженные файлы (фото, аватары, файлы чатов) — только на сервере демо}';

    protected $description = 'Сбросить публичное демо: стереть базу и заполнить демо-данными';

    private const UPLOAD_DIRS = ['large', 'medium', 'small', 'xsmall', 'avatar', 'filemessage', 'filemessage/original'];

    public function handle(): int
    {
        if (!Demo::enabled()) {
            $this->error('demo:reset стирает всю базу — он работает только при DEMO_MODE=true');
            return self::FAILURE;
        }

        if ($this->option('clean-files')) {
            foreach (self::UPLOAD_DIRS as $dir) {
                File::ensureDirectoryExists(public_path($dir));
                foreach (File::files(public_path($dir)) as $file) {
                    if ($file->getFilename() !== '.gitkeep') {
                        File::delete($file->getPathname());
                    }
                }
            }
        }

        $this->call('migrate:fresh', ['--force' => true]);
        $this->call('db:seed', ['--class' => ReferenceDataSeeder::class, '--force' => true]);
        $this->call('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);

        $this->info('Демо сброшено');
        return self::SUCCESS;
    }
}
