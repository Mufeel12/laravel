<?php

/*
   |--------------------------------------------------------------------------
   | Application Routes
   |--------------------------------------------------------------------------
   |
   | Here is where you can register all of the routes for an application.
   | It's a breeze. Simply tell Laravel the URIs it should respond to
   | and give it the controller to call when that URI is requested.
   |
 */

use Illuminate\Http\Request;
use App\Issue;
use App\Statistic;
use App\Video;
use Carbon\Carbon;
use App\Image;
use App\User;

Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
Route::get('phpinfo', function() {phpinfo();});

Route::get('/', 'HomeController@show');
Route::get('/project{any}', 'HomeController@show')->where('any', '.*');
Route::get('/all-project{any}', 'HomeController@show')->where('any', '.*');
Route::get('/settings{any}', 'HomeController@show')->where('any', '.*');
Route::get('/analytics{any}', 'HomeController@show')->where('any', '.*');
Route::get('/subscriber{any}', 'HomeController@show')->where('any', '.*');
Route::get('/login{any}', 'HomeController@show')->where('any', '.*');
Route::get('/register{any}', 'HomeController@show')->where('any', '.*');
Route::get('/admin{any}', 'HomeController@show')->where('any', '.*');


Route::get('/DownloadSrt/{name}', 'Api\VideoController@DownloadSrt')->name('downloadSrt');

/**
 * Public video
 */
Route::get('api/video/show', ['uses' => 'Api\VideoController@show']);
// Unlock
Route::post('api/video/unlock', ['uses' => 'Api\VideoController@unlock']);
// Get vimeo url
//Route::post('api/video/getVimeoUrl', ['uses' => 'Api\VideoController@vimeoUrl']);

/**
 * Video Preview thumbnail
 */
Route::get('/thumbnail/{id}', ['uses' => 'EmbedController@preview']);
Route::get('/thumbnail/popover/{id}', ['uses' => 'EmbedController@thumbnail']);
Route::get('/thumbnail/{id}/w_{width},h_{height}', ['uses' => 'EmbedController@preview']);
Route::get('/projects/edit/video', ['as' => 'editVideo','uses' => 'Api\EditorController@index']);
Route::get('/embed/{id}', ['as' => 'embedVideo','uses' => 'EmbedController@show']);

/**
 * Video
 */
Route::get('/watch/{id}/{startTime}', ['as' => 'showVideo','uses' => 'EmbedController@show']);
Route::get('/watch/{id}', ['as' => 'watchVideo','uses' => 'EmbedController@show']);
Route::get('/watch/{id}/{startTime}', ['as' => 'watchVideo','uses' => 'EmbedController@show']);
Route::get('/watch/thumbnail', ['as' => 'watchPopover','uses' => 'EmbedController@thumbnail']);
Route::get('callback-video', 'VideoController@encoderCallback');
Route::get('/subscription{any}', 'HomeController@show')->where('any', '.*');
Route::get('/logout{any}', 'HomeController@show')->where('any', '.*');
Route::get('/reset-password{any}', 'HomeController@show')->where('any', '.*');
Route::get('/forgot-password{any}', 'HomeController@show')->where('any', '.*');
Route::get('/stage{any}', 'HomeController@show')->where('any', '.*');
Route::get('/contacts{any}', 'HomeController@show')->where('any', '.*');

Route::get('/send-v-email/{user_id}','Api\AuthController@startFreeForever')->where(['user_id' => '[0-9]+']);
Route::get('/social_free_trail/{user_id}','Api\AuthController@socialRegstartFreeForever')->where(['user_id' => '[0-9]+']);
Route::get('/active-account/{user_id}','Api\AuthController@activeAccount')->where(['user_id' => '[0-9]+']);
Route::get('/confirm-email/{hash}/','Api\AuthController@confirmEmailVerification')->where('hash', '.*');

Route::group(['prefix' => 'billing','middleware' => ['cors', 'auth:api'],], function () {
    Route::get('/invoices/send-email/{invoice_id}/{user_id?}', 'InvoiceController@sendEmailInvoice')->where(['invoice_id' => '[0-9]+','user_id' => '[0-9]+',]);
    Route::get('/users/invoices/{action}/{invoice_id}/{user_id?}', 'InvoiceController@viewOrDownloadPdf')->where(['invoice_id' => '[0-9]+','user_id' => '[0-9]+','action' => 'view|download']);
});

Route::fallback('HomeController@show');

Route::get('shared-snaps/{id}', 'Api\SnapController@sharedSnaps');