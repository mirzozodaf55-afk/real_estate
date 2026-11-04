<?php

namespace App\Console\Commands;

use App\Models\Announ;
use App\Models\AnnounNotification;
use App\Models\Likes;
use App\Models\ModerationLog;
use App\Models\Owner;
use Database\Seeders\ModerationDemoSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/** Убрать демо-данные ModerationDemoSeeder: только владельцы demo-…@example.invalid и всё, что к ним относится */
class ModerationDemoClear extends Command
{
    protected $signature = 'moderation:demo-clear';
    protected $description = 'Удалить демо-объявления и демо-владельцев этапа 1 модерации';

    public function handle(): int
    {
        $owners = Owner::where('email', 'like', ModerationDemoSeeder::EMAIL_PATTERN)->pluck('id');
        if ($owners->isEmpty()) {
            $this->info('Демо-данных нет');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($owners, &$count) {
            $ids = Announ::anyStatus()->withTrashed()->whereIn('owner_id', $owners)->pluck('id');
            Likes::whereIn('announ_id', $ids)->orWhereIn('user_id', $owners)->delete();
            AnnounNotification::whereIn('announ_id', $ids)->orWhereIn('user_id', $owners)->delete();
            ModerationLog::whereIn('announ_id', $ids)->delete();
            $count = Announ::anyStatus()->withTrashed()->whereIn('id', $ids)->forceDelete();
            Owner::whereIn('id', $owners)->delete();
        });

        $this->info("Удалено демо-объявлений: $count, демо-владельцев: {$owners->count()}");
        return self::SUCCESS;
    }
}
