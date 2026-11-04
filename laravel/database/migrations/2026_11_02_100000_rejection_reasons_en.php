<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Причины отказа на английском — для сайта в английском режиме (пусто — показываем русский вариант)
        Schema::table('rejection_reasons', function (Blueprint $table) {
            $table->string('title_en', 100)->nullable()->after('text');
            $table->string('text_en', 500)->nullable()->after('title_en');
        });

        // перевод стартовых причин (если админ их не переименовал)
        $en = [
            'Фото не соответствуют объекту' => ['Photos don’t match the property', 'The photos don’t match the property in the listing. Upload real photos of this property.'],
            'Нереальная цена' => ['Unrealistic price', 'The price is far from the market price. Specify the real price of the property.'],
            'Контакты в описании' => ['Contacts in the description', 'The text or photos contain phone numbers, links or messengers. Remove them — your contacts are shown in your profile.'],
            'Дубликат объявления' => ['Duplicate listing', 'This listing has already been posted on the site. Posting the same listing again isn’t allowed.'],
        ];
        foreach ($en as $title => [$titleEn, $textEn]) {
            DB::table('rejection_reasons')->where('title', $title)->whereNull('title_en')->update(['title_en' => $titleEn, 'text_en' => $textEn]);
        }
    }

    public function down(): void
    {
        Schema::table('rejection_reasons', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'text_en']);
        });
    }
};
