<?php

namespace App\Console\Commands;

use App\Models\Announ;
use App\Services\AutoModerator;
use Illuminate\Console\Command;

/**
 * Прогнать автомодерацию по уже ожидающим объявлениям (новые проходят её сами после ИИ-проверок).
 * Берёт только проверенные ИИ — непроверенные сначала: moderation:backfill --ai.
 */
class ModerationAuto extends Command
{
    protected $signature = 'moderation:auto';

    protected $description = 'Автомодерация ожидающих объявлений по уже готовым ИИ-проверкам';

    public function handle(): int
    {
        if (AutoModerator::mode() === AutoModerator::OFF) {
            $this->warn('Автомодерация выключена — включите её в админке (Обзор)');
            return self::INVALID;
        }

        $pending = Announ::anyStatus()->where('moderation_status', Announ::PENDING)->whereNotNull('ai_review_at')->get();
        $this->info("Ждут модерации с ИИ-проверкой: {$pending->count()}");
        $decided = 0;
        foreach ($pending as $announ) {
            $status = AutoModerator::run($announ);
            if ($status) {
                $decided++;
                $this->line("№{$announ->id}: {$status}");
            }
        }
        $this->info("Решено автоматически: $decided, оставлено модератору: " . ($pending->count() - $decided));

        return self::SUCCESS;
    }
}
