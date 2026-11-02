<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Тонкая обёртка над OpenAI-совместимым Chat Completions (ключ, адрес и модель — config/services.php → openai).
 * Работает с OpenAI, Groq, Gemini (OpenAI-режим), Ollama — меняется только OPENAI_BASE_URL и OPENAI_MODEL.
 * Ошибки сети/квоты превращаются в одно исключение с понятным текстом для пользователя.
 */
class OpenAi
{
    public static function enabled(): bool
    {
        return (bool) config('services.openai.key');
    }

    /**
     * @param array $messages  [['role' => 'system'|'user'|'assistant'|'tool', 'content' => ...], ...]
     * @param array $options   tools, response_format, temperature, max_tokens
     * @return array           message из choices[0] (content и/или tool_calls)
     */
    public static function chat(array $messages, array $options = []): array
    {
        if (!self::enabled()) {
            throw new RuntimeException('Domly-помощник пока не настроен');
        }

        $payload = array_filter([
            'model' => $options['model'] ?? config('services.openai.model'),
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.4,
            // с запасом: у «рассуждающих» моделей (gpt-oss, o-серия) размышления тоже входят в лимит
            'max_tokens' => $options['max_tokens'] ?? 2500,
            // low/medium/high — только для рассуждающих моделей; пусто = не отправляем (другие модели его не знают)
            // другая модель из options (например, для картинок) может не знать этот параметр — тогда передают null
            'reasoning_effort' => array_key_exists('reasoning_effort', $options) ? $options['reasoning_effort'] : (config('services.openai.reasoning_effort') ?: null),
            'tools' => $options['tools'] ?? null,
            'tool_choice' => isset($options['tools']) ? ($options['tool_choice'] ?? null) : null,
            'response_format' => $options['response_format'] ?? null,
        ], fn ($v) => $v !== null);

        // Бесплатные тарифы (Groq) ограничивают токены в минуту и обычно просят подождать доли секунды —
        // в таком случае ждём и повторяем, а не сразу показываем пользователю ошибку
        for ($attempt = 0; ; $attempt++) {
            try {
                // свой файл корневых сертификатов: PHP на Windows по умолчанию их не знает (cURL error 60),
                // а php.ini подхватывается только после перезапуска сервера
                $caBundle = config('services.openai.ca_bundle');
                $response = Http::withToken(config('services.openai.key'))
                    ->withOptions(['verify' => $caBundle && is_file($caBundle) ? $caBundle : true])
                    ->acceptJson()
                    ->timeout(45)
                    ->post(rtrim(config('services.openai.base_url'), '/') . '/chat/completions', $payload);
            } catch (\Throwable $e) {
                Log::warning('OpenAI недоступен', ['error' => $e->getMessage()]);
                throw new RuntimeException('Domly-помощник сейчас недоступен, попробуйте позже');
            }

            $wait = $response->status() === 429 ? self::retryAfter($response) : null;
            // до 15 секунд ждём (в чате это выглядит как «печатает…»), дольше — честно говорим «попробуйте через минуту»
            if ($wait === null || $wait > 15 || $attempt >= 2) {
                break;
            }
            usleep((int) (($wait + 0.3) * 1_000_000));
        }

        if (!$response->successful()) {
            $status = $response->status();
            // в лог — подсказка, что чинить; пользователю — общий текст (ключ и модель его не касаются)
            Log::warning('ИИ-провайдер вернул ошибку: ' . match (true) {
                $status === 401 => 'неверный OPENAI_API_KEY',
                $status === 404 => 'нет такой модели OPENAI_MODEL или неверный OPENAI_BASE_URL',
                $status === 429 => 'превышен лимит запросов',
                default => "HTTP $status",
            }, ['body' => mb_strimwidth($response->body(), 0, 500)]);
            // код ответа — в code исключения: json() по 400 пробует запасной режим
            throw new RuntimeException($status === 429
                ? 'Domly-помощник перегружен, попробуйте через минуту'
                : 'Domly-помощник сейчас недоступен, попробуйте позже', $status);
        }

        return $response->json('choices.0.message') ?? [];
    }

    /** Сколько секунд просит подождать провайдер: заголовок retry-after или «try again in 674ms / 1.5s» в тексте ошибки */
    private static function retryAfter($response): ?float
    {
        $header = $response->header('retry-after');
        if (is_numeric($header)) {
            return (float) $header;
        }
        if (preg_match('/try again in ([\d.]+)\s*(ms|s)/i', (string) $response->json('error.message'), $m)) {
            return $m[2] === 'ms' ? $m[1] / 1000 : (float) $m[1];
        }
        return null;
    }

    /**
     * Ответ строго по JSON-схеме — для мест, где фронту нужны поля, а не текст.
     * Не все провайдеры и модели (бесплатные Groq, Ollama…) понимают строгий режим json_schema:
     * если он отклонён, повторяем в обычном JSON-режиме со схемой в тексте и запоминаем это на сутки.
     */
    public static function json(array $messages, string $name, array $schema, array $options = []): array
    {
        $noStrictKey = 'ai_no_json_schema:' . md5(config('services.openai.base_url') . ($options['model'] ?? config('services.openai.model')));

        $message = null;
        if (!Cache::has($noStrictKey)) {
            try {
                $message = self::chat($messages, $options + [
                    'response_format' => [
                        'type' => 'json_schema',
                        'json_schema' => ['name' => $name, 'strict' => true, 'schema' => $schema],
                    ],
                ]);
            } catch (RuntimeException $e) {
                if ($e->getCode() !== 400) {
                    throw $e;
                }
                Cache::put($noStrictKey, true, now()->addDay());
            }
        }

        if ($message === null) {
            $messages[] = ['role' => 'system', 'content' => 'Ответь ТОЛЬКО одним JSON-объектом, без пояснений и markdown, строго по этой JSON-схеме: '
                . json_encode($schema, JSON_UNESCAPED_UNICODE)];
            $message = self::chat($messages, $options + ['response_format' => ['type' => 'json_object']]);
        }

        // некоторые модели (Qwen) пишут размышления в <think>…</think> или оборачивают JSON в ```json — убираем
        $content = preg_replace('/<think>.*?<\/think>/su', '', (string) ($message['content'] ?? ''));
        $content = trim(preg_replace('/^```(?:json)?\s*|\s*```$/u', '', trim($content)));
        $data = json_decode($content, true);
        $missing = array_diff($schema['required'] ?? [], array_keys(is_array($data) ? $data : []));
        if (!is_array($data) || $missing) {
            throw new RuntimeException('ИИ вернул неожиданный ответ, попробуйте ещё раз');
        }
        return $data;
    }
}
