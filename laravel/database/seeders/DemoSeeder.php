<?php

namespace Database\Seeders;

use App\Models\Announ;
use App\Models\AnnounNotification;
use App\Models\Likes;
use App\Models\Message;
use App\Models\messageUser;
use App\Models\ModerationLog;
use App\Models\Owner;
use App\Models\Pictures;
use App\Models\SiteSetting;
use App\Services\DuplicateFinder;
use App\Services\ListingPhotos;
use App\Support\Demo;
use Illuminate\Database\Seeder;
use Intervention\Image\Facades\Image;

/**
 * Данные публичного демо на английском (DEMO_MODE): аккаунты для входа одной кнопкой, объявления в поиске
 * (с VIP и ТОП), очередь модерации с разным риском ИИ и дубликатом, переписка и уведомления.
 * Фото — database/seeders/demo-photos (Pexels, см. LICENSE.txt). Запускается командой demo:reset.
 */
class DemoSeeder extends Seeder
{
    private const PHOTOS = __DIR__ . '/demo-photos';

    public function run(): void
    {
        $buyer = $this->owner('buyer', 'Alex', 'Morgan', 'user');
        $owner = $this->owner('owner', 'Farrukh', 'Rahimov', 'user');
        $moderator = $this->owner('moderator', 'Dilshod', 'Karimov', 'moderator');
        $this->owner('admin', 'Madina', 'Nazarova', 'admin');
        $agent = $this->extraOwner('nigora@demo.domly', 'Nigora', 'Saidova');
        $scammer = $this->extraOwner('quickdeal@demo.domly', 'Quick', 'Deal');

        $flat = ['type_real_estate' => 'Жилая', 'house_type' => 'Монолитный', 'elevator' => 'есть', 'Balcony' => 'есть', 'parking' => 'Наземная'];
        $rent = ['deal_type' => 'Аренда', 'rental_type' => 'Длительно', 'allowKids' => 'Можно с детьми', 'allowAnimal' => 'нет'];
        $daily = ['deal_type' => 'Аренда', 'rental_type' => 'Посуточно', 'allowKids' => 'Можно с детьми', 'allowAnimal' => 'нет'];
        $sale = ['deal_type' => 'Продажа'];

        // --- в поиске
        $vip = $this->listing($owner, $moderator, Announ::APPROVED, $sale + $flat + [
            'type_object' => 'Квартира', 'title' => 'Modern 2-bedroom apartment on Rudaki Avenue',
            'description' => 'Bright 2-bedroom apartment with an open-plan living room and a designer kitchen. Floor-to-ceiling windows, underfloor heating, new appliances. Walking distance to Rudaki Park, cafes and the metro bus line.',
            'price' => 1150000, 'quantity_room' => 2, 'total_area' => 86, 'living_area' => 52, 'kitchen_area' => 14,
            'floor' => 7, 'floor_in_house' => 16, 'year_of_construction' => 2021, 'repair' => 'Дизайнерский',
            'address' => 'Dushanbe, Rudaki Avenue 84', 'coord_x' => 38.5737, 'coord_y' => 68.7738,
        ], ['rudaki-living', 'rudaki-dining', 'rudaki-kitchen'], 'low', 4);
        $vip->forceFill(['promo' => 'vip', 'promo_until' => now()->addDays(14)])->saveQuietly();

        $top = $this->listing($owner, $moderator, Announ::APPROVED, $rent + $flat + [
            'type_object' => 'Квартира', 'title' => 'Bright studio near Dushanbe Mall',
            'description' => 'Furnished studio with a separate sleeping area, washing machine and air conditioning. Quiet courtyard, supermarket and gym in the building. Long-term rent, utilities paid separately.',
            'price' => 3800, 'quantity_room' => 1, 'total_area' => 38, 'living_area' => 24, 'kitchen_area' => 8,
            'floor' => 4, 'floor_in_house' => 12, 'year_of_construction' => 2019, 'repair' => 'Евроремонт',
            'address' => 'Dushanbe, Ismoili Somoni Avenue 48', 'coord_x' => 38.5810, 'coord_y' => 68.7580,
        ], ['studio-living', 'studio-bedroom'], 'low', 3);
        $top->forceFill(['promo' => 'top', 'promo_until' => now()->addDays(7)])->saveQuietly();

        $house = $this->listing($agent, $moderator, Announ::APPROVED, $sale + [
            'type_real_estate' => 'Жилая', 'type_object' => 'Дом/Дача', 'title' => 'Family house with a garden in Varzob',
            'description' => 'Two-storey family house with four bedrooms, a large kitchen-dining room and a garage for two cars. Fruit garden, mountain air and 25 minutes to the city centre.',
            'price' => 2400000, 'quantity_room' => 5, 'total_area' => 240, 'land_area' => 10, 'year_of_construction' => 2016,
            'repair' => 'Евроремонт', 'address' => 'Varzob district, Ziddi road 12', 'coord_x' => 38.7540, 'coord_y' => 68.8170,
        ], ['varzob-house', 'varzob-kitchen', 'varzob-hall'], 'low', 5);

        $view = $this->listing($agent, $moderator, Announ::APPROVED, $rent + $flat + [
            'type_object' => 'Квартира', 'title' => '3-room apartment with a mountain view',
            'description' => 'Spacious apartment on a high floor with views of the mountains. Two bedrooms, a living room with a smart TV and a fully equipped kitchen. Families welcome.',
            'price' => 6500, 'quantity_room' => 3, 'total_area' => 104, 'living_area' => 68, 'kitchen_area' => 15,
            'floor' => 14, 'floor_in_house' => 18, 'year_of_construction' => 2022, 'repair' => 'Евроремонт',
            'address' => 'Dushanbe, Bokhtar Street 37', 'coord_x' => 38.5640, 'coord_y' => 68.7900,
        ], ['view-living', 'view-kitchen'], 'low', 6);

        $this->listing($owner, $moderator, Announ::APPROVED, $daily + $flat + [
            'type_object' => 'Квартира', 'title' => 'Cozy 1-bedroom for daily rent in the centre',
            'description' => 'Clean and cozy apartment for short stays: fresh linen, Wi-Fi, kitchen with everything you need. Self check-in, 5 minutes to the Navruz Palace.',
            'price' => 450, 'quantity_room' => 1, 'total_area' => 45, 'floor' => 3, 'floor_in_house' => 9,
            'year_of_construction' => 2015, 'repair' => 'Евроремонт',
            'address' => 'Dushanbe, Sheroz Street 15', 'coord_x' => 38.5550, 'coord_y' => 68.8010,
        ], ['daily-living', 'daily-bedroom'], 'low', 8);

        $this->listing($owner, $moderator, Announ::APPROVED, $sale + $flat + [
            'type_object' => 'Квартира в Новостройке', 'title' => 'Loft with brick walls in a new building',
            'description' => 'Loft-style apartment with high ceilings, exposed brick and large windows. Open kitchen with an island. The building was finished this year, all documents are ready.',
            'price' => 890000, 'quantity_room' => 2, 'total_area' => 72, 'kitchen_area' => 18,
            'floor' => 5, 'floor_in_house' => 10, 'year_of_construction' => 2026, 'repair' => 'Дизайнерский',
            'address' => 'Dushanbe, Mirzo Tursunzoda Street 22', 'coord_x' => 38.5770, 'coord_y' => 68.7850,
        ], ['loft-living', 'loft-kitchen'], 'low', 10);

        $this->listing($agent, $moderator, Announ::APPROVED, $sale + [
            'type_real_estate' => 'Жилая', 'type_object' => 'Дом/Дача', 'title' => 'Country house near the river',
            'description' => 'Classic country house with a cozy attic bedroom, a veranda and an old orchard. Ten minutes on foot to the river. Ideal for summer and weekends.',
            'price' => 780000, 'quantity_room' => 3, 'total_area' => 120, 'land_area' => 6, 'year_of_construction' => 2008,
            'repair' => 'Косметический', 'address' => 'Hisor, Navobod village 3', 'coord_x' => 38.5260, 'coord_y' => 68.5510,
        ], ['river-house', 'river-attic'], 'low', 12);

        $this->listing($agent, $moderator, Announ::APPROVED, $sale + [
            'type_real_estate' => 'Жилая', 'type_object' => 'Коттедж', 'title' => 'Townhouse with a garage and a terrace',
            'description' => 'New townhouse in a gated community: three bedrooms, a kitchen with breakfast bar, a terrace and a double garage. Security and playground on site.',
            'price' => 1650000, 'quantity_room' => 4, 'total_area' => 180, 'land_area' => 3, 'year_of_construction' => 2023,
            'repair' => 'Евроремонт', 'address' => 'Dushanbe, Sino district, Karamov Street 101', 'coord_x' => 38.5900, 'coord_y' => 68.7300,
        ], ['townhouse', 'townhouse-kitchen'], 'low', 14);

        $this->listing($agent, $moderator, Announ::APPROVED, $sale + [
            'type_real_estate' => 'Коммерческая', 'type_object' => 'Офис', 'title' => 'Office floor in a modern business centre',
            'description' => 'Open-space office of 210 m² on the 2nd floor: panoramic windows, two meeting rooms, separate entrance and parking for clients.',
            'price' => 2900000, 'total_area' => 210, 'floor' => 2, 'floor_in_house' => 5, 'year_of_construction' => 2020,
            'address' => 'Dushanbe, Ayni Street 54', 'coord_x' => 38.5680, 'coord_y' => 68.7950,
        ], ['office-building'], 'low', 16);

        // --- очередь модерации: высокий и средний риск, дубликат текста и фото, без проверки ИИ
        $sinoText = 'Two-room apartment in Sino, 54 m², third floor of a nine-storey building. Fresh renovation, furniture and appliances, a school and a bus stop nearby.';
        $this->listing($scammer, null, Announ::PENDING, $rent + $flat + [
            'type_object' => 'Квартира', 'title' => '2-room apartment, prepayment for 3 months',
            'description' => 'Urgent, I am leaving the country. Pay for 3 months in advance and the keys are yours today. Write to me on Telegram.',
            'price' => 900, 'quantity_room' => 2, 'total_area' => 60, 'floor' => 5, 'floor_in_house' => 9,
            'address' => 'Dushanbe, 46th microdistrict',
        ], ['scam-living'], 'high', 1, [
            'Цена в 4 раза ниже медианы похожих объявлений, просьба о предоплате и переписке вне сайта — частые признаки мошенничества.',
            ['Цена в 4 раза ниже рынка', 'Предоплата за 3 месяца', 'Просит писать в Telegram'],
            'Цена и условия похожи на мошенничество. Укажите реальную цену и уберите требование предоплаты.',
            'The price is 4x below similar listings, and the owner asks for prepayment and contact outside the site — typical signs of fraud.',
            ['Price 4x below the market', 'Prepayment for 3 months', 'Asks to continue on Telegram'],
            'The price and terms look like fraud. Please state the real price and remove the prepayment requirement.',
        ]);
        $this->listing($agent, null, Announ::PENDING, $rent + $flat + [
            'type_object' => 'Квартира', 'title' => '2-room apartment in Sino, 54 m²', 'description' => $sinoText,
            'price' => 3600, 'quantity_room' => 2, 'total_area' => 54, 'floor' => 3, 'floor_in_house' => 9,
            'address' => 'Dushanbe, Sino district, Firdavsi Street 9',
        ], ['sino-living'], 'medium', 2, [
            'Похоже на правду, но в описании мало деталей о доме.', ['Не указан год постройки'], '',
            'Looks genuine, but the description has few details about the building.', ['Year of construction is missing'], '',
        ]);
        // копия: тот же текст, что у предыдущего, и одно фото из объявления на Рудаки — DuplicateFinder покажет оба совпадения
        $this->listing($scammer, null, Announ::PENDING, $rent + $flat + [
            'type_object' => 'Квартира', 'title' => '2-room apartment in Sino, 54 m², renovated', 'description' => $sinoText,
            'price' => 3500, 'quantity_room' => 2, 'total_area' => 54, 'floor' => 3, 'floor_in_house' => 9,
            'address' => 'Dushanbe, Sino district',
        ], ['sino-living-2', 'rudaki-living'], 'medium', 2, [
            'Текст и одно фото совпадают с другими объявлениями.', ['Возможный дубликат'], '',
            'The text and one photo match other listings.', ['Possible duplicate'], '',
        ]);
        $this->listing($owner, null, Announ::PENDING, $rent + $flat + [
            'type_object' => 'Квартира', 'title' => 'Studio in central Dushanbe',
            'description' => 'Cozy 28 m² studio on the 5th floor of a new building with a lift and parking. Furniture and appliances included.',
            'price' => 2800, 'quantity_room' => 1, 'total_area' => 28, 'floor' => 5, 'floor_in_house' => 12,
            'address' => 'Dushanbe, Pushkin Street 18',
        ], ['centre-studio'], 'low', 3, [
            'Проблем не найдено.', [], '', 'No problems found.', [], '',
        ]);
        $this->listing($agent, null, Announ::PENDING, $rent + $flat + [
            'type_object' => 'Комната', 'title' => 'Room for a student near the university',
            'description' => 'Furnished room in a three-room apartment, 5 minutes to the Tajik National University. Quiet neighbours, Wi-Fi included.',
            'price' => 800, 'quantity_room' => 1, 'total_area' => 16, 'address' => 'Dushanbe, Rudaki Avenue 17',
        ], ['student-room'], null, 1);

        $needsChanges = $this->listing($owner, $moderator, Announ::NEEDS_CHANGES, $sale + [
            'type_real_estate' => 'Жилая', 'type_object' => 'Дом/Дача', 'title' => 'House with a plot in Vahdat',
            'description' => 'House of 120 m² on a plot of 8 hundredths, gas, water and electricity connected.',
            'price' => 450000, 'quantity_room' => 4, 'total_area' => 120, 'land_area' => 8, 'address' => 'Vahdat, Navbahor Street 5',
        ], ['vahdat-house'], 'low', 20, null, 'Please add photos of the rooms and of the plot — buyers want to see more than the facade.');

        $this->listing($scammer, $moderator, Announ::REJECTED, $sale + $flat + [
            'type_object' => 'Квартира', 'title' => 'Cheap apartment, deposit first',
            'description' => 'Send a deposit to my card and I will show the apartment.',
            'price' => 50000, 'quantity_room' => 3, 'total_area' => 70, 'address' => 'Dushanbe',
        ], [], 'high', 30, null, 'The listing asks for a deposit before a viewing. This is not allowed on Domly.');

        // --- покупатель: избранное и переписка с владельцем VIP-объявления
        foreach ([$vip, $house, $view] as $liked) {
            Likes::forceCreate(['user_id' => $buyer->id, 'announ_id' => $liked->id]);
        }
        $this->chat($buyer, $owner, [
            [$buyer, 'Hi! Is the apartment on Rudaki Avenue still available?', 95],
            [$owner, 'Hello! Yes, it is. Would you like to come and see it?', 90],
            [$buyer, 'Yes, is tomorrow at 6 pm OK? And is the price negotiable?', 30],
            [$owner, 'Tomorrow at 6 works. We can talk about the price on the spot.', 25],
            [$buyer, 'Great, see you tomorrow!', 20],
        ]);

        // --- колокольчик владельца
        AnnounNotification::forceCreate(['user_id' => $owner->id, 'announ_id' => $vip->id, 'kind' => AnnounNotification::KIND_PROMOTED, 'message' => 'VIP до ' . $vip->promo_until->format('d.m.Y'), 'created_at' => now()->subHours(3)]);
        AnnounNotification::forceCreate(['user_id' => $owner->id, 'announ_id' => $needsChanges->id, 'kind' => AnnounNotification::KIND_NEEDS_CHANGES, 'message' => $needsChanges->moderation_reason, 'created_at' => now()->subHours(20)]);
        AnnounNotification::forceCreate(['user_id' => $owner->id, 'announ_id' => $top->id, 'kind' => AnnounNotification::KIND_APPROVED, 'read_at' => now()->subDay(), 'created_at' => now()->subDays(2)]);

        // новые объявления из демо ждут модератора — так видно всю цепочку: ИИ-проверка → очередь → решение
        SiteSetting::put('auto_moderation', 'off');
    }

    private function owner(string $role, string $name, string $lastName, string $staffRole): Owner
    {
        return $this->extraOwner(Demo::ACCOUNTS[$role], $name, $lastName, $staffRole);
    }

    private function extraOwner(string $email, string $name, string $lastName, string $role = 'user'): Owner
    {
        $owner = Owner::forceCreate([
            'email' => $email, 'name' => $name, 'last_name' => $lastName, 'role' => $role,
            'uniq_id' => (string) random_int(10000000, 99999999),
            'phone_number' => '+992 90 ' . random_int(100, 999) . ' ' . random_int(10, 99) . ' ' . random_int(10, 99),
            'created_at' => now()->subMonths(3),
        ]);
        return $owner;
    }

    /**
     * @param array|null $review [summary, issues, suggested_reason, summary_en, issues_en, suggested_reason_en] — вывод ИИ;
     *                           для одобренных без своего текста — «проблем не найдено»
     */
    private function listing(Owner $owner, ?Owner $moderator, string $status, array $fields, array $photos, ?string $risk, int $hoursAgo, ?array $review = null, ?string $reason = null): Announ
    {
        $review ??= $risk ? ['Проблем не найдено.', [], '', 'No problems found.', [], ''] : null;
        $at = now()->subHours($hoursAgo);

        $announ = new Announ();
        $announ->forceFill($fields + [
            'owner_id' => $owner->id,
            'moderation_status' => $status,
            'moderation_reason' => $reason,
            'moderated_at' => $moderator ? $at->copy()->addMinutes(40) : null,
            'moderated_by' => $moderator?->id,
            'ai_risk' => $risk,
            'ai_review' => $review ? [
                'risk' => $risk,
                'summary' => $review[0], 'issues' => $review[1], 'suggested_reason' => $review[2],
                'summary_en' => $review[3], 'issues_en' => $review[4], 'suggested_reason_en' => $review[5],
            ] : null,
            'ai_review_at' => $review ? $at->copy()->addMinutes(2) : null,
            'created_at' => $at,
            'updated_at' => $at,
        ]);
        $announ->timestamps = false;
        $announ->save();

        foreach ($photos as $position => $photo) {
            $name = "demo-$photo.jpg";
            foreach (ListingPhotos::SIZES as $folder => [$w, $h]) {
                $path = public_path("$folder/$name");
                if (!is_file($path)) { // одно фото может быть у двух объявлений (дубликат) — нарезаем один раз
                    Image::make(self::PHOTOS . "/$photo.jpg")->fit($w, $h)->save($path, 82);
                }
            }
            Pictures::forceCreate(['announ_id' => $announ->id, 'pictures' => $name, 'position' => $position, 'rotation' => 0]);
        }
        DuplicateFinder::hashPhotos($announ);

        if ($moderator) {
            ModerationLog::forceCreate([
                'moderator_id' => $moderator->id,
                'action' => match ($status) { Announ::APPROVED => 'approve', Announ::NEEDS_CHANGES => 'needs_changes', default => 'reject' },
                'announ_id' => $announ->id,
                'target_user_id' => $owner->id,
                'reason' => $reason,
                'created_at' => $at->copy()->addMinutes(40),
            ]);
        }
        return $announ;
    }

    /** @param array $lines [[отправитель, текст, минут назад], ...] */
    private function chat(Owner $a, Owner $b, array $lines): void
    {
        foreach ($lines as $i => [$from, $text, $minutesAgo]) {
            $at = now()->subMinutes($minutesAgo);
            $message = Message::forceCreate(['message' => $text, 'user_id' => $from->id, 'type' => 'message', 'time' => $at->format('H:i'), 'created_at' => $at, 'updated_at' => $at]);
            $to = $from->is($a) ? $b : $a;
            // последнее сообщение владелец ещё не прочитал — у него будет счётчик непрочитанных
            messageUser::forceCreate(['message_id' => $message->id, 'owner_id' => $from->id, 'friend_id' => $to->id, 'read_at' => $i < count($lines) - 1 ? $at : null, 'created_at' => $at, 'updated_at' => $at]);
        }
    }
}
