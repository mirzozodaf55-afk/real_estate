<?php
/*https://payouts.payoneer.com/partners/Default/AccountRegistration.aspx?token=4c0c7b583ea645198351dace367f8c33B995A4DD71&ref=or&_gl=1*2lf5zd*_up*MQ..*_gs*MQ..&gclid=CjwKCAjwl_XBBhAUEiwAWK2hzpiLptBydKnIjZEHmvk1ptUmAfEaEJ03lB0-7VLxSSCbwBuhTBiIoBoC6kEQAvD_BwE&gbraid=0AAAAADmHOcgsB1KFfqvFwzsAfWx7giaSB*/

use App\Http\Controllers\CreateAnnounController;
use App\Http\Controllers\CreateOwnerController;
use App\Http\Controllers\createPictureController;
use App\Http\Controllers\deleteAnnounController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\getAnnounController;
use App\Http\Controllers\getDataController;
use App\Http\Controllers\getImageController;
use App\Http\Controllers\LikesController;
use App\Http\Controllers\UpdateAnnounController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotifyController;
use App\Http\Controllers\VideoController;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Translation\MessageCatalogue;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/avatar',[getImageController::class, 'getAvatar'])->middleware('auth:sanctum');
Route::group(['middleware' => ['auth:sanctum', 'not.blocked']], function(){
    Route::post('/create/announ',[CreateAnnounController::class, 'createResidentAnnoun'])->middleware('throttle:1000,1');
    Route::post('/save-pictures',[getImageController::class, 'saveImageFiles'])->middleware('throttle:1000,1');
    Route::post('/upload-image',[getImageController::class,'createImageFiles'])->middleware('throttle:1000,1');
    Route::post('/update_user',[CreateOwnerController::class,'UpdateOwner'])->middleware('throttle:1000,1');
    Route::get('/get_user',[CreateOwnerController::class,'getUser'])->middleware('throttle:1000,1');
    Route::get('/get_announs_by_user',[CreateAnnounController::class,'getAnnounByUser'])->middleware('throttle:1000,1');
    // Мои объявления — все действия только над своими объявлениями
    Route::get('/my/announs',[\App\Http\Controllers\MyAnnounController::class,'index']);
    Route::get('/my/announs/{id}',[\App\Http\Controllers\MyAnnounController::class,'show'])->whereNumber('id');
    // Продвижение (ТОП/VIP): заявка владельца, оплата пока вручную
    Route::get('/my/promotions', [\App\Http\Controllers\PromotionController::class, 'mine']);
    Route::post('/my/announs/{id}/promote', [\App\Http\Controllers\PromotionController::class, 'store'])->whereNumber('id')->middleware('throttle:my-announ');
    Route::post('/my/announs/{id}',[\App\Http\Controllers\MyAnnounController::class,'update'])->whereNumber('id')->middleware('throttle:my-announ');
    Route::post('/my/announs/{id}/unpublish',[\App\Http\Controllers\MyAnnounController::class,'unpublish'])->whereNumber('id');
    Route::post('/my/announs/{id}/publish',[\App\Http\Controllers\MyAnnounController::class,'publish'])->whereNumber('id');
    Route::post('/my/announs/{id}/sold',[\App\Http\Controllers\MyAnnounController::class,'markSold'])->whereNumber('id');
    Route::post('/my/announs/{id}/unsold',[\App\Http\Controllers\MyAnnounController::class,'unmarkSold'])->whereNumber('id');
    Route::delete('/my/announs/{id}',[\App\Http\Controllers\MyAnnounController::class,'destroy'])->whereNumber('id');
    Route::post('/send-message',[MessageController::class, 'Message'])->middleware('throttle:1000,1');
    Route::post('/send-file',[MessageController::class,'SendFile'])->middleware('throttle:1000,1');
    Route::post('/send-video',[MessageController::class,'SendVideo']);
    Route::post('/get-message',[MessageController::class, 'getMessage'])->middleware('throttle:1000,1');
    Route::post('/typing',[MessageController::class, 'Typing'])->middleware('throttle:1000,1');
    Route::get('/messages/unread',[MessageController::class, 'unread']);
    Route::post('/messages/read',[MessageController::class, 'markRead']);
    // Route::get('/get_all_users',[MessageController::class,'getAllUser'])->middleware('throttle:1000,1');
    Route::post('/notify-me',[NotifyController::class, 'notifyMe'])->middleware('throttle:notify-me');
    Route::get('/notify-me',[NotifyController::class, 'index']);
    Route::delete('/notify-me/{id}',[NotifyController::class, 'destroy'])->whereNumber('id');
    Route::get('/notifications',[NotifyController::class, 'notifications']);
    Route::post('/notifications/read',[NotifyController::class, 'markRead']);
    Route::post('/delete-acount',[CreateAnnounController::class, 'deleteAcount'])->middleware('throttle:1000,1');
    Route::post('/get_video',[MessageController::class, 'getVideo']);
    Route::post('/likes',[LikesController::class,'setLikes']);
    Route::get('/check-auth',function(Request $request){
        return response()->json([
            'isAuth'=>true,
            'user'=>$request->user()
        ]);
    });
    Route::get('/delete_acount', function(Request $request){
        $request->user()->currentAccessToken()->delete();
        return ['curentAccessToken deleted'];
    });
    Route::get('/favorite', [LikesController::class, 'getFavorite']);
    // ИИ пишет заголовок и описание объявления — только вошедшим, чтобы не жгли ключ анонимно
    Route::post('/ai/describe', [\App\Http\Controllers\AiController::class, 'describe'])->middleware('throttle:ai-write');
    Route::post('/ai/publish', [\App\Http\Controllers\AiController::class, 'publish'])->middleware('throttle:ai-write');

    // Админка: модерация — moderator и admin, пользователи и роли — только admin
    Route::prefix('admin')->middleware('role:moderator,admin')->group(function () {
        Route::get('/stats', [\App\Http\Controllers\Admin\ModerationController::class, 'stats']);
        Route::get('/announs', [\App\Http\Controllers\Admin\ModerationController::class, 'index']);
        Route::get('/announs/{id}', [\App\Http\Controllers\Admin\ModerationController::class, 'show'])->whereNumber('id');
        Route::post('/announs/{id}/approve', [\App\Http\Controllers\Admin\ModerationController::class, 'approve'])->whereNumber('id');
        Route::post('/announs/{id}/reject', [\App\Http\Controllers\Admin\ModerationController::class, 'reject'])->whereNumber('id');
        Route::post('/announs/{id}/needs-changes', [\App\Http\Controllers\Admin\ModerationController::class, 'needsChanges'])->whereNumber('id');
        Route::post('/announs/bulk', [\App\Http\Controllers\Admin\ModerationController::class, 'bulk']);
        Route::get('/rejection-reasons', [\App\Http\Controllers\Admin\RejectionReasonController::class, 'index']);
        Route::post('/announs/{id}/ai-review', [\App\Http\Controllers\AiController::class, 'review'])->whereNumber('id')->middleware('throttle:ai-review');
        Route::post('/announs/{id}/photo-check', [\App\Http\Controllers\Admin\ModerationController::class, 'photoCheck'])->whereNumber('id')->middleware('throttle:photo-check');

        Route::middleware('role:admin')->group(function () {
            Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index']);
            Route::post('/users/{id}/role', [\App\Http\Controllers\Admin\UserController::class, 'setRole'])->whereNumber('id');
            Route::post('/users/{id}/block', [\App\Http\Controllers\Admin\UserController::class, 'block'])->whereNumber('id');
            // Готовые причины отказа — редактирует только админ
            Route::post('/rejection-reasons', [\App\Http\Controllers\Admin\RejectionReasonController::class, 'store']);
            Route::put('/rejection-reasons/{id}', [\App\Http\Controllers\Admin\RejectionReasonController::class, 'update'])->whereNumber('id');
            Route::delete('/rejection-reasons/{id}', [\App\Http\Controllers\Admin\RejectionReasonController::class, 'destroy'])->whereNumber('id');
            // Продвижение: заявки, тарифы и реквизиты
            Route::get('/promotions', [\App\Http\Controllers\Admin\PromotionController::class, 'index']);
            Route::post('/promotions/{id}/approve', [\App\Http\Controllers\Admin\PromotionController::class, 'approve'])->whereNumber('id');
            Route::post('/promotions/{id}/reject', [\App\Http\Controllers\Admin\PromotionController::class, 'reject'])->whereNumber('id');
            Route::post('/promotions/settings', [\App\Http\Controllers\Admin\PromotionController::class, 'settings']);
            Route::delete('/announs/{id}/promo', [\App\Http\Controllers\Admin\PromotionController::class, 'cancel'])->whereNumber('id');
            // Оформление: фон главной
            Route::post('/settings/hero', [\App\Http\Controllers\SiteSettingsController::class, 'updateHero']);
            Route::delete('/settings/hero', [\App\Http\Controllers\SiteSettingsController::class, 'deleteHero']);
            Route::post('/users/{id}/unblock', [\App\Http\Controllers\Admin\UserController::class, 'unblock'])->whereNumber('id');
            // Переписки пользователей — только просмотр, каждый просмотр пишется в журнал
            Route::get('/chats', [\App\Http\Controllers\Admin\ChatController::class, 'index']);
            Route::get('/chats/{a}/{b}', [\App\Http\Controllers\Admin\ChatController::class, 'show'])->whereNumber(['a', 'b']);
        });
    });
});
Route::get('/get_all_users',[MessageController::class,'getAllUser'])->middleware('throttle:1000,1');
Route::post('/video-sync',[VideoController::class, 'syncVideo']);
Route::get('/avatar/{name}',function($name){
    // basename — чтобы через имя нельзя было выйти из папки avatar
    $path = public_path('avatar/' . basename($name));
    if (!is_file($path)) {
        // Нет файла (у новых пользователей по умолчанию "avatar.jpg", которого нет) — серый силуэт вместо 500
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" fill="#e2e8f0"/><circle cx="50" cy="38" r="18" fill="#94a3b8"/><path d="M18 92c4-18 18-28 32-28s28 10 32 28" fill="#94a3b8"/></svg>';
        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }
    return response()->file($path);
});

Route::get('/filemessage/{size}/{name}',function($size,$name){
    return response()->download(public_path("filemessage/$size/$name",));
});
Route::get('/filemessage/{name}',function($name){
    return response()->download(public_path("filemessage/$name",));
});



Route::post('/get_user_with_announ',[CreateOwnerController::class,'getOwnerFromAnnoun']);
Route::post('/get_announs',[CreateAnnounController::class,'getAnnouns']);
Route::post('/announs/search',[\App\Http\Controllers\AnnounSearchController::class,'search'])->middleware('throttle:search');
Route::get('/announs/{id}',[\App\Http\Controllers\AnnounSearchController::class,'show'])->whereNumber('id')->middleware('throttle:show-announ');
// ИИ-помощник по поиску доступен и без входа; лимит — чтобы один посетитель не израсходовал баланс OpenAI
Route::get('/ai/status',[\App\Http\Controllers\AiController::class,'status']);
Route::get('/site-settings',[\App\Http\Controllers\SiteSettingsController::class,'index']);
Route::post('/ai/chat',[\App\Http\Controllers\AiController::class,'chat'])->middleware('throttle:ai-chat');
Route::get('/get_data',[getAnnounController::class, 'getData']);
// Не чаще 5 писем в минуту с одного IP, чтобы через нас не спамили чужие ящики
Route::post('/create_user', [CreateOwnerController::class, 'createOwner'])->middleware('throttle:create-user');
// Вход через Google / Apple (SocialAuthController)
Route::get('/promo/plans', [\App\Http\Controllers\PromotionController::class, 'plans']);
Route::get('/auth/providers', [\App\Http\Controllers\SocialAuthController::class, 'providers']);
Route::post('/auth/google', [\App\Http\Controllers\SocialAuthController::class, 'google'])->middleware('throttle:verify-user');
Route::post('/auth/apple', [\App\Http\Controllers\SocialAuthController::class, 'apple'])->middleware('throttle:verify-user');
Route::post('/verify-user', [CreateOwnerController::class, 'verifyUser'])->middleware('throttle:verify-user');
Route::post('/filter',[FilterController::class,'FiltersAnnoun']);



Route::get('/city', [getDataController::class, 'getCities']);
Route::post('/create/city', [getDataController::class,'createCity']);
Route::post('/create/furniture', [getDataController::class,'createFurniture']);

Route::post('/send_message',[CreateOwnerController::class,'SendMessage'])->middleware('auth:sanctum');



Route::get('/conditions',[getDataController::class ,'getCondition']);
Route::get('/infrastructure', [getDataController::class,'getInfrastrucures']);
Route::post('/create/infrastructure', [getDataController::class,'createInfrastrucures']);

Route::get('/image/{folder}/{name}',function($folder, $name){
    return response()->download(public_path("$folder/$name",));
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});