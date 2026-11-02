<?php

namespace App\Console\Commands;

use App\Models\Announ;
use App\Services\DuplicateFinder;
use App\Services\ListingReviewer;
use App\Services\OpenAi;
use Illuminate\Console\Command;

/**
 * Досчитать для уже существующих объявлений то, что новые получают сами при подаче:
 * pHash фото (поиск дубликатов) и ИИ-проверку текста и цены (риск в очереди модерации).
 */
class ModerationBackfill extends Command
{
    protected $signature = 'moderation:backfill
        {--hashes : посчитать pHash для всех фото без него}
        {--ai : ИИ-проверка текста и цены для ожидающих модерации без проверки}
        {--sleep=8 : пауза между запросами к ИИ, сек (бесплатный Groq — ~8000 токенов в минуту)}';

    protected $description = 'pHash фото и ИИ-риск для объявлений, поданных до этапа 1 модерации';

    public function handle(): int
    {
        if (!$this->option('hashes') && !$this->option('ai')) {
            $this->warn('Укажите --hashes и/или --ai');
            return self::INVALID;
        }

        if ($this->option('hashes')) {
            $ids = Announ::anyStatus()->withTrashed()->whereHas('pictures', fn ($q) => $q->whereNull('phash'))->pluck('id');
            $this->info("Фото без pHash в объявлениях: {$ids->count()}");
            $bar = $this->output->createProgressBar($ids->count());
            foreach ($ids as $id) {
                DuplicateFinder::hashPhotos(Announ::anyStatus()->withTrashed()->find($id));
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
        }

        if ($this->option('ai')) {
            if (!OpenAi::enabled()) {
                $this->error('ИИ не настроен (OPENAI_API_KEY)');
                return self::FAILURE;
            }
            $pending = Announ::anyStatus()->where('moderation_status', Announ::PENDING)->whereNull('ai_review_at')->get();
            $this->info("Ждут модерации без ИИ-проверки: {$pending->count()}");
            foreach ($pending as $i => $announ) {
                try {
                    $review = ListingReviewer::review($announ, true);
                    $this->line("№{$announ->id}: риск {$review['risk']} — {$review['summary']}");
                } catch (\Throwable $e) {
                    $this->warn("№{$announ->id}: не удалось — {$e->getMessage()}");
                }
                if ($i < $pending->count() - 1) {
                    sleep((int) $this->option('sleep'));
                }
            }
        }

        return self::SUCCESS;
    }
}
