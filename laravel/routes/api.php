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

Route::post('/avatar',[getImageController::class, 'getAvatar']);
Route::group(['middleware' => 'auth:sanctum'], function(){
    Route::post('/create/announ/commercial', [CreateAnnounController::class, 'createAnnoun'])->middleware('throttle:1000,1');
    Route::post('/create/announ',[CreateAnnounController::class, 'createResidentAnnoun'])->middleware('throttle:1000,1');
    Route::post('/save-pictures',[getImageController::class, 'saveImageFiles'])->middleware('throttle:1000,1');
    Route::post('/upload-image',[getImageController::class,'createImageFiles'])->middleware('throttle:1000,1');
    Route::post('/update_user',[CreateOwnerController::class,'UpdateOwner'])->middleware('throttle:1000,1');
    Route::get('/get_user',[CreateOwnerController::class,'getUser'])->middleware('throttle:1000,1');
    Route::get('/get_announs_by_user',[CreateAnnounController::class,'getAnnounByUser'])->middleware('throttle:1000,1');
    Route::post('/delete-announ',[deleteAnnounController::class,'deleteAnnoun'])->middleware('throttle:1000,1');
    Route::post('/update-announ',[UpdateAnnounController::class,'updateAnnoun'])->middleware('throttle:1000,1');
    Route::post('/restore',[UpdateAnnounController::class,'restoreData'])->middleware('throttle:1000,1');
    Route::post('/send-message',[MessageController::class, 'Message'])->middleware('throttle:1000,1');
    Route::post('/send-file',[MessageController::class,'SendFile'])->middleware('throttle:1000,1');
    Route::post('/send-video',[MessageController::class,'SendVideo']);
    Route::post('/get-message',[MessageController::class, 'getMessage'])->middleware('throttle:1000,1');
    Route::post('/typing',[MessageController::class, 'Typing'])->middleware('throttle:1000,1');
    // Route::get('/get_all_users',[MessageController::class,'getAllUser'])->middleware('throttle:1000,1');
    Route::post('/notify-me',[NotifyController::class, 'notifyMe'])->middleware('throttle:1000,1');
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
});
Route::get('/get_all_users',[MessageController::class,'getAllUser'])->middleware('throttle:1000,1');
Route::post('/video-sync',[VideoController::class, 'syncVideo']);
Route::get('/avatar/{name}',function($name){
    return response()->download(public_path("avatar/$name",));
});

Route::get('/filemessage/{size}/{name}',function($size,$name){
    return response()->download(public_path("filemessage/$size/$name",));
});
Route::get('/filemessage/{name}',function($name){
    return response()->download(public_path("filemessage/$name",));
});



Route::post('/get-data-info',[UpdateAnnounController::class,'getDataInfo']);
Route::post('/get_user_with_announ',[CreateOwnerController::class,'getOwnerFromAnnoun']);
Route::post('/get_announs',[CreateAnnounController::class,'getAnnouns']);
Route::get('/get_data',[getAnnounController::class, 'getData']);
Route::post('/create_user', [CreateOwnerController::class, 'createOwner']);
Route::post('/verify-user', [CreateOwnerController::class, 'verifyUser']);
Route::post('/filter',[FilterController::class,'FiltersAnnoun']);



Route::get('/city', [getDataController::class, 'getCities']);
Route::post('/create/city', [getDataController::class,'createCity']);
Route::post('/create/furniture', [getDataController::class,'createFurniture']);

Route::post('/send_message',[CreateOwnerController::class,'SendMessage']);



Route::get('/conditions',[getDataController::class ,'getCondition']);
Route::get('/infrastructure', [getDataController::class,'getInfrastrucures']);
Route::post('/create/infrastructure', [getDataController::class,'createInfrastrucures']);

Route::get('/image/{folder}/{name}',function($folder, $name){
    return response()->download(public_path("$folder/$name",));
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});