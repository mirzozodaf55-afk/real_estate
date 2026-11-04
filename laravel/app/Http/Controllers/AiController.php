<?php

namespace App\Http\Controllers;

use App\Models\Announ;
use App\Services\AnnounFilter;
use App\Services\ListingPhotos;
use App\Services\OpenAi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * ИИ-помощник:
 *  - chat     — подбор объявлений, ответы о функциях сайта и черновик объявления (посетители, в том числе без входа);
 *  - publish  — публикация черновика из чата с фото (только вошедшим; уходит на модерацию);
 *  - describe — заголовок и описание объявления для продавца;
 *  - review   — предварительная проверка объявления для модератора.
 */
class AiController extends Controller
{
    private const DEALS = ['Продажа', 'Аренда', 'Посуточно'];
    private const TYPES = ['Квартира', 'Квартира в Новостройке', 'Комната', 'Дом/Дача', 'Коттедж', 'Часть дома', 'Участок',
        'Офис', 'Здание', 'Торговая площадь', 'Склад', 'Коммерческая земля'];
    private const MAX_TOOL_ROUNDS = 3;
    private const LISTINGS_LIMIT = 6;

    public function status()
    {
        return response()->json(['enabled' => OpenAi::enabled()]);
    }

    // --- Чат-помощник по поиску

    public function chat(Request $request)
    {
        $data = $request->validate([
            'messages' => 'required|array|min:1|max:12',
            'messages.*.role' => 'required|in:user,assistant',
            'messages.*.content' => 'required|string|max:1500',
            'lang' => 'nullable|in:ru,en', // язык сайта — на нём и отвечаем
        ]);

        $user = $request->user('sanctum');
        $loggedIn = $user && !$user->isBlocked();

        $messages = [['role' => 'system', 'content' => $this->chatPrompt($loggedIn, $data['lang'] ?? 'ru')]];
        foreach ($data['messages'] as $m) {
            $messages[] = ['role' => $m['role'], 'content' => $m['content']];
        }
        // подать объявление можно только вошедшему — иначе функцию даже не показываем модели
        $tools = $loggedIn ? [$this->searchTool(), $this->prepareListingTool()] : [$this->searchTool()];

        // Явная просьба найти — поиск обязателен: иначе модель любит сначала переспрашивать цену и комнаты
        $lastUser = mb_strtolower(collect($data['messages'])->where('role', 'user')->last()['content'] ?? '');
        $mustSearch = preg_match('/найд|найт|ищу|поиск|подбер|покаж|есть ли|сним|снять|куп(лю|ить)|посуточ/u', $lastUser)
            && !preg_match('/сдать|сдаю|продать|продаю|размест|подать|опублик/u', $lastUser);

        $listings = null;
        $draft = null;
        try {
            // Модель сама решает, какую функцию вызвать; выполняем её мы (поиск по базе, проверка черновика)
            for ($round = 0; $round <= self::MAX_TOOL_ROUNDS; $round++) {
                $reply = OpenAi::chat($messages, [
                    'tools' => $round < self::MAX_TOOL_ROUNDS ? $tools : null,
                    'tool_choice' => $round === 0 && $mustSearch ? ['type' => 'function', 'function' => ['name' => 'search_listings']] : null,
                    'temperature' => 0.3,
                ]);
                $calls = $reply['tool_calls'] ?? [];
                if (!$calls) {
                    break;
                }
                $messages[] = ['role' => 'assistant', 'content' => $reply['content'] ?? null, 'tool_calls' => $calls];
                foreach ($calls as $call) {
                    $args = json_decode($call['function']['arguments'] ?? '{}', true) ?: [];
                    if (($call['function']['name'] ?? '') === 'prepare_listing' && $loggedIn) {
                        [$normalized, $errors] = $this->normalizeDraft($args);
                        // в браузер — в формате аргументов функции: с ним черновик вернётся в publish и проверится заново
                        $draft = $errors ? null : $this->draftForClient($normalized);
                        $result = $errors
                            ? ['ok' => false, 'missing_or_invalid' => $errors, 'hint' => 'Уточни у пользователя эти данные и вызови prepare_listing снова']
                            : ['ok' => true, 'note' => 'Черновик показан пользователю карточкой. Попроси проверить данные, добавить фото и нажать «Опубликовать». Сам ты не публикуешь.'];
                    } else {
                        [$found, $total, $droppedQuery] = $this->searchListings($args);
                        $listings = $found;
                        $result = ['total' => $total, 'listings' => $found->map(fn ($a) => $this->forModel($a))->values()];
                        if ($droppedQuery !== null) {
                            $result['note'] = "По «{$droppedQuery}» объявлений нет. Ниже — подходящие по остальным условиям, но в ДРУГИХ местах: прямо скажи об этом и не выдавай их за «{$droppedQuery}».";
                        }
                    }
                    $messages[] = ['role' => 'tool', 'tool_call_id' => $call['id'], 'content' => json_encode($result, JSON_UNESCAPED_UNICODE)];
                }
            }
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }

        return response()->json([
            'reply' => $this->plainText($reply['content'] ?? '') ?: 'Не получилось сформулировать ответ, попробуйте переформулировать вопрос.',
            'listings' => $listings ? $listings->map(fn ($a) => $this->forCard($a, $request))->values() : [],
            'draft' => $draft,
        ]);
    }

    // --- Подача объявления через помощника: ИИ готовит черновик, человек проверяет, добавляет фото и публикует

    private const RESIDENTIAL_FLATS = ['Квартира', 'Квартира в Новостройке', 'Комната'];
    private const COMMERCIAL = ['Офис', 'Здание', 'Торговая площадь', 'Склад', 'Коммерческая земля'];

    private function prepareListingTool(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'prepare_listing',
                'description' => 'Готовит черновик объявления пользователя. Пользователь увидит карточку, добавит фото и сам нажмёт «Опубликовать».',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'deal' => ['type' => 'string', 'enum' => self::DEALS, 'description' => 'Продажа; Аренда — надолго; Посуточно'],
                        'type_object' => ['type' => ['string', 'null'], 'enum' => [...self::TYPES, null]],
                        'price' => ['type' => ['number', 'null'], 'description' => 'Цена в сомони (для аренды — за месяц, посуточно — за сутки)'],
                        'rooms' => ['type' => ['number', 'null']],
                        'total_area' => ['type' => ['number', 'null'], 'description' => 'Общая площадь, м²'],
                        'living_area' => ['type' => ['number', 'null']],
                        'kitchen_area' => ['type' => ['number', 'null']],
                        'floor' => ['type' => ['number', 'null']],
                        'floors_in_house' => ['type' => ['number', 'null']],
                        'year' => ['type' => ['number', 'null'], 'description' => 'Год постройки'],
                        'land_area' => ['type' => ['number', 'null'], 'description' => 'Площадь участка, сотки'],
                        'repair' => ['type' => ['string', 'null'], 'enum' => ['Без ремонта', 'Косметический', 'Евроремонт', 'Дизайнерский', null], 'description' => 'Только если пользователь сам сказал про ремонт; иначе не передавай'],
                        'title' => ['type' => 'string', 'description' => 'Заголовок до 80 символов — только по фактам от пользователя'],
                        'description' => ['type' => 'string', 'description' => '2–5 честных предложений только по фактам пользователя, с городом/районом если он назвал; без телефонов'],
                    ],
                    'required' => ['deal', 'type_object', 'price', 'title', 'description'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    /**
     * Проверяет и приводит черновик к полям таблицы announs. Используется дважды:
     * когда ИИ готовит черновик и когда пользователь публикует (черновик приходит из браузера — ему не доверяем).
     * @return array{0: array, 1: array}  [поля для announs, список ошибок]
     */
    private function normalizeDraft(array $a): array
    {
        $errors = [];
        $int = function ($key, $min, $max) use ($a) {
            return isset($a[$key]) && is_numeric($a[$key]) && $a[$key] >= $min && $a[$key] <= $max ? (int) $a[$key] : null;
        };

        $deal = in_array($a['deal'] ?? null, self::DEALS, true) ? $a['deal'] : null;
        $type = in_array($a['type_object'] ?? null, self::TYPES, true) ? $a['type_object'] : null;
        $title = trim((string) ($a['title'] ?? ''));
        $description = trim((string) ($a['description'] ?? ''));
        $draft = [
            'deal_type' => $deal === 'Посуточно' ? 'Аренда' : $deal,
            'rental_type' => $deal === 'Посуточно' ? 'Посуточно' : ($deal === 'Аренда' ? 'Длительно' : null),
            'type_object' => $type,
            'type_real_estate' => in_array($type, self::COMMERCIAL, true) ? 'Коммерческая' : 'Жилая',
            'price' => $int('price', 1, 2000000000),
            'quantity_room' => $int('rooms', 0, 50),
            'total_area' => $int('total_area', 1, 100000),
            'living_area' => $int('living_area', 1, 100000),
            'kitchen_area' => $int('kitchen_area', 1, 10000),
            'floor' => $int('floor', -5, 300),
            'floor_in_house' => $int('floors_in_house', 1, 300),
            'year_of_construction' => $int('year', 1800, 2100),
            'land_area' => $int('land_area', 1, 1000000),
            'repair' => in_array($a['repair'] ?? null, ['Без ремонта', 'Косметический', 'Евроремонт', 'Дизайнерский'], true) ? $a['repair'] : null,
            'title' => mb_substr($title, 0, 150),
            'description' => mb_substr($description, 0, 3000),
        ];

        if (!$deal) $errors[] = 'тип сделки (продажа, аренда или посуточно)';
        if (!$type) $errors[] = 'тип объекта';
        if (!$draft['price']) $errors[] = 'цена в сомони';
        if (in_array($type, self::RESIDENTIAL_FLATS, true)) {
            if ($draft['quantity_room'] === null) $errors[] = 'количество комнат';
            if (!$draft['total_area']) $errors[] = 'общая площадь';
        }
        if (in_array($type, ['Участок', 'Коммерческая земля'], true) && !$draft['land_area']) $errors[] = 'площадь участка в сотках';
        if ($draft['floor'] && $draft['floor_in_house'] && $draft['floor'] > $draft['floor_in_house']) $errors[] = 'этаж больше этажности дома';
        if (mb_strlen($title) < 6) $errors[] = 'заголовок (от 6 символов)';
        if (mb_strlen($description) < 15) $errors[] = 'описание (от 15 символов)';
        if (preg_match('/(\+?\d[\d\s\-()]{7,}\d)|https?:\/\/|t\.me\//u', $title . ' ' . $description)) $errors[] = 'убрать телефоны и ссылки из текста — контакты видны в профиле';

        return [array_filter($draft, fn ($v) => $v !== null), $errors];
    }

    /** Проверенный черновик обратно в формат prepare_listing (deal, rooms, …) — его показывает и присылает браузер */
    private function draftForClient(array $d): array
    {
        return array_filter([
            'deal' => ($d['rental_type'] ?? null) === 'Посуточно' ? 'Посуточно' : $d['deal_type'],
            'type_object' => $d['type_object'],
            'price' => $d['price'],
            'rooms' => $d['quantity_room'] ?? null,
            'total_area' => $d['total_area'] ?? null,
            'living_area' => $d['living_area'] ?? null,
            'kitchen_area' => $d['kitchen_area'] ?? null,
            'floor' => $d['floor'] ?? null,
            'floors_in_house' => $d['floor_in_house'] ?? null,
            'year' => $d['year_of_construction'] ?? null,
            'land_area' => $d['land_area'] ?? null,
            'repair' => $d['repair'] ?? null,
            'title' => $d['title'],
            'description' => $d['description'],
        ], fn ($v) => $v !== null);
    }

    public function publish(Request $request)
    {
        $request->validate([
            'draft' => 'required|string|max:10000',
            'photos' => 'required|array|min:1|max:' . ListingPhotos::MAX,
            'photos.*' => 'file|image|max:20480',
        ], [
            'photos.required' => 'Добавьте хотя бы одно фото',
            'photos.*.image' => 'Можно загружать только фотографии',
            'photos.*.max' => 'Фото должно быть не больше 20 МБ',
        ]);

        [$draft, $errors] = $this->normalizeDraft(json_decode($request->input('draft'), true) ?: []);
        if ($errors) {
            return response()->json(['message' => 'Не хватает данных: ' . implode(', ', $errors)], 422);
        }

        $announ = DB::transaction(function () use ($draft, $request) {
            $announ = new Announ();
            // статус модерации не задаём — по умолчанию «на проверке», как у обычной подачи
            $announ->forceFill($draft + ['owner_id' => $request->user()->id])->save();
            foreach (array_values($request->file('photos')) as $position => $file) {
                ListingPhotos::store($file, $announ->id, $position);
            }
            return $announ;
        });
        \App\Services\ListingChecks::dispatch($announ->id); // pHash фото, ИИ-проверка фото и текста/цены → риск в очереди

        return response()->json(['message' => 'Объявление отправлено на проверку модератору', 'id' => $announ->id], 201);
    }

    /** Модели всё равно иногда отвечают с markdown — убираем разметку, чтобы в чате не было звёздочек и решёток */
    private function plainText(string $text): string
    {
        $text = preg_replace('/\*\*(.+?)\*\*|__(.+?)__/u', '$1$2', $text);
        $text = preg_replace('/^\s{0,3}#{1,6}\s+/mu', '', $text);
        $text = preg_replace('/^\s*[*\-]\s+/mu', '• ', $text);
        return trim($text);
    }

    private function chatPrompt(bool $loggedIn, string $lang = 'ru'): string
    {
        // Инструкция уходит с каждым сообщением, а бесплатный Groq даёт 8000 токенов в минуту — поэтому кратко
        return implode("\n", [
            'Ты Domly-помощник — помощник сайта недвижимости Domly в Таджикистане. ' . ($lang === 'en'
                ? 'Сайт открыт на английском — ВСЕГДА отвечай по-английски, даже если пишут по-русски (названия и адреса из объявлений оставляй как есть). Цены пиши словом somoni, без значков валют'
                : 'Отвечай на языке пользователя (русский/таджикский)') . ', 2–5 предложений, обычным текстом без markdown.',
            'ПОИСК: сразу вызывай search_listings с тем, что уже известно, — не спрашивай цену и комнаты до поиска; уточнить можно после, по результатам. Советуй только найденное. Цены в сомони. Город/район — в query. rooms 4 = «4+». Карточки видны под ответом — выдели 1–3 лучших. Пусто — предложи ослабить условия или «Уведомить меня».',
            'ПОДАЧА: ' . ($loggedIn
                ? 'пользователь вошёл. Не переспрашивай то, что он уже сказал. Спрашивай только недостающее, не больше 2 вопросов за раз: сделка, тип, цена; для квартиры/комнаты — комнаты, площадь, этаж/этажность; район; ремонт и мебель. Не передавай поля, о которых он не говорил. '
                    . 'Потом сам напиши честные заголовок и описание по его словам и вызови prepare_listing. Он увидит карточку, добавит фото и сам нажмёт «Опубликовать» — уйдёт на модерацию. Не говори, что уже опубликовано.'
                : 'пользователь не вошёл — пусть нажмёт «Войти» (код на почту), потом поможешь подать прямо здесь; или «Разместить объявление» — пошаговая форма.'),
            'САЙТ: вход по 6-значному коду на почту, паролей нет. Поиск: на главной и в списке (фильтры, сортировка, «Ещё фильтры», «На карте»).',
            '«Уведомить меня» в списке — сохраняет поиск, о новых объявлениях — колокольчик и письмо; управление: Личный кабинет → «Сохранённые поиски».',
            'Избранное — сердечко. Сообщения — написать владельцу со страницы объявления, все чаты — иконка вверху (фото и видео можно).',
            '«Разместить объявление» — пошаговая форма, на шаге описания кнопка «Написать текст с ИИ».',
            'Модерация: новое и изменённое объявление скрыто до проверки; решение — уведомлением и письмом; причину отказа видно в «Моих объявлениях», после правки — снова на проверку.',
            'Мои объявления: статусы, редактирование, «Снять с публикации» и «Продано/Сдано» (скрывают из поиска, можно вернуть), «Удалить навсегда».',
            'Профиль: имя, фото, «Выйти», «Удалить аккаунт». За мошенничество/спам аккаунт блокируют.',
            'Другие темы — вежливо откажи. Не сообщай чужие телефоны и почту.',
        ]);
    }

    private function searchTool(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'search_listings',
                'description' => 'Ищет опубликованные объявления в базе сайта по условиям пользователя.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'deal_type' => ['type' => ['string', 'null'], 'enum' => [...self::DEALS, null], 'description' => 'Продажа — купить; Аренда — снять надолго; Посуточно — снять на сутки'],
                        'type_object' => ['type' => ['string', 'null'], 'enum' => [...self::TYPES, null]],
                        // null тоже допустим: модель часто передаёт null вместо пустого списка, и Groq отклонял весь вызов (HTTP 400)
                        'rooms' => ['type' => ['array', 'null'], 'items' => ['type' => 'number'], 'description' => 'Количество комнат; 4 = 4 и больше'],
                        'price_from' => ['type' => ['number', 'null'], 'description' => 'Цена от, сомони'],
                        'price_to' => ['type' => ['number', 'null'], 'description' => 'Цена до, сомони'],
                        'query' => ['type' => ['string', 'null'], 'description' => \App\Support\Demo::enabled()
                            // в публичном демо объявления на английском (Database\Seeders\DemoSeeder)
                            ? 'City, district, street or features (renovated, furnished, near a park). Only in English — listings are in English: Душанбе → Dushanbe'
                            : 'Город, район, улица или особенности (ремонт, мебель, у парка). Только по-русски — база на русском: Dushanbe → Душанбе, Khujand → Худжанд'],
                        'sort' => ['type' => ['string', 'null'], 'enum' => ['new', 'price_asc', 'price_desc', null]],
                    ],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    /** @return array{0: \Illuminate\Support\Collection, 1: int, 2: ?string} третье — текст запроса, если по нему пусто и искали без него */
    private function searchListings(array $args): array
    {
        $filters = array_filter([
            'dealType' => in_array($args['deal_type'] ?? null, self::DEALS, true) ? $args['deal_type'] : null,
            'typeObject' => in_array($args['type_object'] ?? null, self::TYPES, true) ? $args['type_object'] : null,
            'quantityRoom' => array_map('strval', array_filter((array) ($args['rooms'] ?? []), 'is_numeric')),
            'priceFrom' => isset($args['price_from']) ? (int) $args['price_from'] : null,
            'priceTo' => isset($args['price_to']) ? (int) $args['price_to'] : null,
            'q' => isset($args['query']) ? mb_substr(trim((string) $args['query']), 0, 80) : null,
        ], fn ($v) => $v !== null && $v !== '' && $v !== []);

        $query = AnnounFilter::apply(Announ::query(), $filters);
        $total = (clone $query)->count();

        // по тексту ищется только точная фраза — если с ней пусто, повторяем без неё, чтобы не терять остальные условия
        $droppedQuery = null;
        if ($total === 0 && isset($filters['q'])) {
            $droppedQuery = $filters['q'];
            unset($filters['q']);
            $query = AnnounFilter::apply(Announ::query(), $filters);
            $total = (clone $query)->count();
        }

        match ($args['sort'] ?? 'new') {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->promotedFirst()->latest(),
        };

        $found = $query->with(['pictures:id,announ_id,pictures,rotation,position', 'owner:id,name,last_name,avatar,uniq_id,phone_number,email'])
            ->withCount('likes')
            ->limit(self::LISTINGS_LIMIT)
            ->get();

        return [$found, $total, $droppedQuery];
    }

    /** Коротко для модели — без фото и контактов */
    private function forModel(Announ $a): array
    {
        return array_filter([
            'id' => $a->id,
            'title' => $a->title,
            'deal' => $a->deal_type === 'Аренда' && $a->rental_type === 'Посуточно' ? 'Посуточно' : $a->deal_type,
            'type' => $a->type_object,
            'price' => $a->price,
            'rooms' => $a->quantity_room,
            'area_m2' => $a->total_area ?: $a->house_area,
            'floor' => $a->floor && $a->floor_in_house ? "{$a->floor}/{$a->floor_in_house}" : null,
            'repair' => $a->repair,
            'description' => $a->description ? mb_strimwidth($a->description, 0, 220, '…') : null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /** Как в обычном поиске (AnnounSearchController): фронт рисует ту же карточку */
    private function forCard(Announ $a, Request $request): array
    {
        $data = $a->toArray();
        if ($a->owner && !$request->user('sanctum')) {
            unset($data['owner']['phone_number'], $data['owner']['email']);
        }
        return $data;
    }

    // --- Помощь продавцу с текстом

    public function describe(Request $request)
    {
        $data = $request->validate([
            'facts' => 'required|array|max:60',
            'lang' => 'nullable|in:ru,en',
            'title' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:5000',
        ]);

        // берём только простые значения — в форме бывают массивы фото, координаты и т.п.
        $facts = [];
        foreach ($data['facts'] as $key => $value) {
            if (is_scalar($value) && $value !== '' && $value !== null && !preg_match('/coord|image|photo|picture|video|token|phone|email/i', (string) $key)) {
                $facts[mb_substr((string) $key, 0, 40)] = mb_substr((string) $value, 0, 200);
            }
        }

        try {
            $result = OpenAi::json([
                ['role' => 'system', 'content' => implode("\n", [
                    'Ты помогаешь владельцу написать объявление о недвижимости в Таджикистане. Пиши '
                        . (($data['lang'] ?? 'ru') === 'en' ? 'по-английски (характеристики даны по-русски — переведи)' : 'по-русски') . ', грамотно и честно.',
                    'СТРОГО: каждое утверждение в тексте должно опираться на характеристики или черновик владельца. Покупатель приедет и проверит.',
                    'Не добавляй ничего, чего нет в данных: окна, подъезд, соседи, тишина, транспорт, парковка, магазины, инфраструктура, «светлая», «уютная», «качественный» — только если это указано.',
                    'Лучше короткое правдивое описание, чем длинное с выдумками. Можно кратко сказать, кому подойдёт, если это следует из фактов (например, рядом школа — удобно семьям).',
                    'Если фактов мало — напиши 2 предложения и остановись. Никаких общих рекламных фраз («современный комфорт», «тихий дом», «отличный вариант»).',
                    'Заголовок — до 80 символов, без восклицательных знаков и капса. Описание — 2–5 предложений, без телефонов, ссылок и цены.',
                    'Сохрани все детали из черновика владельца, но исправь в них орфографию и регистр: каждое предложение и заголовок — с заглавной буквы.',
                ])],
                ['role' => 'user', 'content' => json_encode([
                    'характеристики' => $facts,
                    'черновик_заголовка' => $data['title'] ?? '',
                    'черновик_описания' => $data['description'] ?? '',
                ], JSON_UNESCAPED_UNICODE)],
            ], 'listing_text', [
                'type' => 'object',
                'properties' => [
                    'title' => ['type' => 'string'],
                    'description' => ['type' => 'string'],
                ],
                'required' => ['title', 'description'],
                'additionalProperties' => false,
            ], ['temperature' => 0.3]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }

        return response()->json([
            'title' => mb_substr($this->sentenceCase($result['title']), 0, 150),
            'description' => mb_substr($this->sentenceCase($result['description']), 0, 3000),
        ]);
    }

    /** Заглавная буква в начале текста и после точки — модель иногда подражает черновику, написанному строчными */
    private function sentenceCase(string $text): string
    {
        $text = trim($text);
        $upper = fn ($s) => mb_strtoupper(mb_substr($s, 0, 1)) . mb_substr($s, 1);
        $text = $upper($text);
        return preg_replace_callback('/([.!?]\s+)(\p{Ll})/u', fn ($m) => $m[1] . mb_strtoupper($m[2]), $text);
    }

    // --- Проверка для модератора

    public function review(Request $request, int $id)
    {
        $announ = Announ::anyStatus()->withTrashed()->findOrFail($id);
        try {
            // ?force=1 — «Перепроверить»: спросить ИИ заново, даже если объявление не менялось
            return response()->json(\App\Services\ListingReviewer::review($announ, $request->boolean('force')));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }
    }
}
