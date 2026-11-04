<?php

namespace Database\Factories;

use App\Models\Announ;
use App\Models\Owner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Объявление для тестов. По умолчанию ждёт модерации, как только что поданное: Announ::factory()->approved()->create()
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Announ>
 */
class AnnounFactory extends Factory
{
    public function definition(): array
    {
        return [
            'owner_id' => Owner::factory(),
            'deal_type' => 'Продажа',
            'type_real_estate' => 'Квартира',
            'title' => fake()->unique()->sentence(5), // разные заголовки — иначе DuplicateFinder примет объявления за копии
            'description' => fake()->sentence(12),
            'price' => fake()->numberBetween(100000, 900000),
            'address' => 'Душанбе, ' . fake()->streetAddress(),
            // в БД это значение по умолчанию, но без него у только что созданной модели статус null
            'moderation_status' => Announ::PENDING,
        ];
    }

    // фабрика создаёт модель без $fillable — служебные поля задаются как обычные
    public function approved(): static
    {
        return $this->status(Announ::APPROVED);
    }

    public function status(string $status): static
    {
        return $this->state(['moderation_status' => $status]);
    }

    /**
     * С выводами ИИ, которые должны видеть только модераторы (Announ::STAFF_FIELDS).
     * Как ListingReviewer: проверка идёт после сохранения и не трогает updated_at — иначе AutoModerator
     * сочтёт её устаревшей (ai_review_at раньше updated_at).
     */
    public function reviewedByAi(string $risk = 'high'): static
    {
        return $this->afterCreating(function ($announ) use ($risk) {
            $announ->forceFill([
                'ai_risk' => $risk,
                'ai_review' => ['risk' => $risk, 'issues' => ['подозрительно низкая цена'], 'suggested_reason' => 'Цена не похожа на настоящую'],
                'ai_review_at' => now(),
            ]);
            $announ->timestamps = false;
            $announ->save();
            $announ->timestamps = true;
        });
    }
}
