<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/do_signup', [ApiController::class, 'seller_registration']);

Route::post('/do_login', [ApiController::class, 'user_login']);

Route::post('/do_forgot_password', [ApiController::class, 'user_forgot_password']);

Route::post('/do_reset_password', [ApiController::class, 'resetPassword']);

Route::post('/get_video_course_data', [ApiController::class, 'getVideoCourseData']);
Route::post('/get_video_course_data_load_more', [ApiController::class, 'load_moreVideos']);
Route::post('/video_course_detail/{id}', [ApiController::class, 'videoCourseDetail']);

Route::post('/get_recipie_data', [ApiController::class, 'getRecipieData']);
Route::post('/recipie_detail/{id}', [ApiController::class, 'recipieDetail']);

Route::post('/get_podcast_data', [ApiController::class, 'getUserPodcastData']);
Route::post('/get_podcast_data_load_more', [ApiController::class, 'podcast_load_moreVideos']);


Route::post('/get_favourite_data/{type}', [ApiController::class, 'favouriteItemAdded']);
Route::post('/get_favourite_data_list/{type}', [ApiController::class, 'favouriteItemList']);

Route::post('/podcast_detail/{id}', [ApiController::class, 'userPodcastDetail']);

Route::post('/get_common_data/{type}', [ApiController::class, 'getCommonPageData']);
Route::post('/get_common_data_load_more/{type}', [ApiController::class, 'getCommonPageLoadMoreVideos']);

Route::post('/common_detail/{id}/{type}', [ApiController::class, 'commonPageDetail']);

// FavouriteItem
Route::post('/get_favourite_items_data', [ApiController::class, 'favouriteItemData']);

Route::post('/get_favourite_items_list/{type}', [ApiController::class, 'favouriteItemListData']);

Route::post('/get_favourite_items_load_more', [ApiController::class, 'favouriteItemLoadMoreVideos']);

Route::post('/newsletter', [ApiController::class, 'newsletter']);

Route::post('/profile', [ApiController::class, 'profileUpdate']);
Route::post('/get_Profile/{user_id}', [ApiController::class, 'getProfile']);

Route::post('/change_push_notification_status', [ApiController::class, 'changePushNotificationStatus']);

Route::get('/test', function(){
	echo "hello";
});





