# Архитектура Domly

## Общая схема

```
Браузер ── Nuxt 3 (SPA, :3000) ──HTTP──▶ Laravel API (:8000) ──▶ MySQL
                │                              │
                └──── websocket (:6001) ◀──────┤  уведомления, чат
                                               ├──▶ Groq (ИИ: текст, фото)
                                               └──▶ SMTP (письма)
```

- Авторизация — токены Laravel Sanctum (срок `SANCTUM_EXPIRATION`, по умолчанию 30 дней). Вход: код на почту, Google, Apple.
- Посетители видят только опубликованное: глобальный scope `App\Models\Scopes\PublicScope` (одобрено модератором, не продано,
  владелец не заблокирован). Код владельца и модераторов снимает его явно: `Announ::anyStatus()`.
- Тяжёлое (ИИ-проверки, письма) выполняется после ответа пользователю: `dispatch(fn)->afterResponse()`.

## Роли

| Роль | Может |
|---|---|
| `user` | подавать и редактировать свои объявления, избранное, чат, сохранённые поиски, продвижение |
| `moderator` | очередь модерации: одобрить, отклонить, на доработку, массовые действия, ИИ-проверки |
| `admin` | всё модератора + пользователи и роли, блокировка, переписки, оформление, продвижение, причины отказа |

Проверка — middleware `role:moderator,admin` и `role:admin` (`App\Http\Middleware\EnsureRole`) на группах маршрутов в `routes/api.php`.

## Модерация (этап 1)

### Жизненный цикл объявления

```
подача / правка владельцем ──▶ pending ──┬──▶ approved        (видно в поиске)
                                         ├──▶ rejected        (причина, письмо)
                                         └──▶ needs_changes   (комментарий, письмо)
rejected / needs_changes ── правка владельцем ──▶ pending
```

Статус — `announs.moderation_status`, причина отказа или комментарий «на доработку» — `announs.moderation_reason`.
Любая правка возвращает объявление в `pending` (`MyAnnounController::update`).

### Проверки после подачи — `App\Services\ListingChecks::dispatch($id)`

Вызывается при подаче (`CreateAnnounController`, `AiController::publish`) и правке (`MyAnnounController::update`):

1. `DuplicateFinder::hashPhotos` — pHash каждого фото (`App\Services\ImageHash`: 32×32 серое → ДКП → 8×8 → 64 бита).
2. `PhotoChecker::check` — ИИ смотрит до 3 фото → `announs.photo_check` (`ok` / `suspicious` / `mismatch`).
3. `ListingReviewer::review` — ИИ проверяет текст и цену против рынка → `announs.ai_review` (`risk`: `low` / `medium` / `high`).
4. `ListingReviewer::refreshRisk` — общий риск `announs.ai_risk` = худшее из двух проверок
   (фото: `mismatch` → high, `suspicious` → medium, `ok` → low). Нет проверок — `null` («не проверено»).

Сбой одной проверки не мешает остальным; модератор может перепроверить кнопкой.

### Автомодерация — `App\Services\AutoModerator::run`

Последний шаг `ListingChecks`. Режим — `site_settings.auto_moderation`, меняет админ на странице «Обзор»
(`POST /api/admin/settings/auto-moderation`):

| Режим | Что делает |
|---|---|
| `off` (по умолчанию) | всё решает модератор |
| `approve` | одобряет, если `ai_risk = low`, фото проверены и `ok` (или фото нет), дубликатов нет, владельцу раньше не отказывали и он не заблокирован |
| `full` | без модератора: `ai_risk = high` — отказ (причина — `ai_review.suggested_reason`, из-за фото или список `ai_review.issues`); есть дубликаты — ждёт модератора; иначе одобряет |

В режиме `approve` всё остальное остаётся в очереди; в любом режиме без свежей ИИ-проверки объявление ждёт модератора. Решение пишется как обычное, но `moderated_by` и `moderation_logs.moderator_id` — `null`
(в истории — «Автомодерация (ИИ)»). Владелец получает те же уведомления и письма.

### Очередь

`GET /api/admin/announs` — для `pending` сортировка: `high` → `medium` → `low` → не проверено, внутри — кто дольше ждёт (`updated_at`).

### Дубликаты — `App\Services\DuplicateFinder::find`

- **Фото:** расстояние Хэмминга между pHash в MySQL (`BIT_COUNT(CONV(...) ^ CONV(...))`), похожие — до 10 бит из 64.
- **Текст:** у объявлений того же типа — заголовок (`similar_text` ≥ 90%) или описание (доля общих троек слов ≥ 60%).

Результат — в карточке модерации (`GET /api/admin/announs/{id}` → `duplicates`).

## Таблицы

Основные (до этапа 1): `owners` (пользователи, `role`, `blocked_at`), `announs` (объявления), `pictures` (фото),
`likes` (избранное, уникально `user_id + announ_id`), `message_users` / `messages` (чат), `notify_mes` (сохранённые поиски),
`announ_notifications` (колокольчик), `moderation_logs` (журнал действий модераторов), `site_settings` (настройки сайта),
`promotion_requests` (заявки ТОП/VIP), `deleted_accounts` (sha256 почты удалённых аккаунтов, запрет на 30 дней).

Добавлено на этапе 1 (`2026_10_31_200000_moderation_stage1`):

| Таблица / поле | Что хранит |
|---|---|
| `announs.ai_review` (json) | ответ ИИ по тексту и цене: `risk`, `summary`, `issues`, `suggested_reason`, `market` |
| `announs.ai_review_at` | когда проверено (свежая, если не позже `updated_at`) |
| `announs.ai_risk` | общий риск для сортировки очереди: `high` / `medium` / `low` / `null` |
| `announs.moderation_status` | + значение `needs_changes` |
| `pictures.phash` | pHash фото, 16 hex-символов |
| `rejection_reasons` | готовые причины отказа: `title` (для модератора), `text` (для владельца), `sort`, `active` |

## Эндпоинты модерации

Все — с токеном; `moderator` и `admin`, если не указано иное.

| Метод и путь | Что делает |
|---|---|
| `GET /api/admin/stats` | счётчики: ждут проверки, на доработке, высокий риск… |
| `GET /api/admin/announs?status=&risk=&from=&to=&q=&page=` | очередь / архив. `status`: `pending` `needs_changes` `approved` `rejected`; `risk`: `high` `medium` `low` `none` |
| `GET /api/admin/announs/{id}` | карточка: объявление, `ai_review`, `photo_check`, `duplicates`, история, статистика владельца |
| `POST /api/admin/announs/{id}/approve` | одобрить |
| `POST /api/admin/announs/{id}/reject` | отклонить: `reason_id` (готовая причина) и/или `reason` (свой текст ≥ 5 символов) |
| `POST /api/admin/announs/{id}/needs-changes` | на доработку: `comment` |
| `POST /api/admin/announs/bulk` | массово: `ids[]` (до 50), `action` = `approve` / `reject` (+ `reason_id` / `reason`) |
| `POST /api/admin/announs/{id}/ai-review[?force=1]` | ИИ-проверка текста и цены (сохранённая или заново) |
| `POST /api/admin/announs/{id}/photo-check` | ИИ-проверка фото заново |
| `GET /api/admin/rejection-reasons[?all=1]` | причины отказа (`all=1` — с выключенными, только админ) |
| `POST /api/admin/rejection-reasons` | **admin**: добавить (`title`, `text`) |
| `PUT /api/admin/rejection-reasons/{id}` | **admin**: изменить (`title`, `text`, `active`, `sort`) |
| `DELETE /api/admin/rejection-reasons/{id}` | **admin**: удалить |

Решения модератора владелец получает в колокольчик (`announ_notifications.kind`: `approved`, `rejected`, `needs_changes`),
по websocket и письмом (`App\Mail\ModerationResultMail`). В письме об отказе и доработке кнопка «Исправить объявление»
ведёт в редактирование: `{FRONTEND_URL}/update-announ?id={id}`.

## Интерфейс

| Файл | Что это |
|---|---|
| `Nuxt-project/pages/admin/announs.vue` | модерация: вкладки статусов, фильтры риска и дат, массовые действия, карточка |
| `Nuxt-project/components/ReasonPicker.vue` | выбор причины отказа (готовая / «Другое») |
| `Nuxt-project/pages/admin/reasons.vue` | справочник причин отказа (админ) |
| `Nuxt-project/components/MyAnnouns.vue` | у владельца: статус «Нужна доработка» с комментарием |

## Команды

| Команда | Что делает |
|---|---|
| `moderation:backfill --hashes` | pHash для фото без него |
| `moderation:backfill --ai [--sleep=8]` | ИИ-проверка текста и цены для ожидающих без проверки |
| `moderation:auto` | автомодерация уже ожидающих объявлений с готовой ИИ-проверкой |
| `moderation:demo-clear` | удалить демо-данные `ModerationDemoSeeder` |
| `user:role {email} {role}` | назначить роль |
| `sanctum:prune-expired` | удалить просроченные токены (по расписанию раз в сутки) |
