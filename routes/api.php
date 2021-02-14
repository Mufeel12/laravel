<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register the API routes for your application as
| the routes are automatically authenticated using the API guard and
| loaded automatically by this application's RouteServiceProvider.
|
*/

use App\Video;
use App\SharedSnap;
use App\Events\SharedSnapRecorded;
use App\Experiment\VideoExperiment;

Route::group(['middleware' => ['cors', 'auth:api', 'authAdmin'], 'prefix' => 'admin'], function () {
        Route::get('/tags/search/active', 'Api\SearchController@searchTags');
        Route::get('/plan-status/search/{status}', 'Api\SearchController@searchPlanStatus')
            ->where([
            'status' => 'active|suspended|deleted',
            ]);
        Route::get('/users/filter/{status}/{id?}', 'Api\Admin\UsersController@getActiveUsers')
            ->where([
                'status' => 'active|suspended|deleted',
                'id' => '[0-9]+',
            ]);
        Route::put('/users/{id}/subscription', 'Api\AuthController@changeSubscription')->where(['id' => '[0-9]+']);
        Route::get('/users/last', 'Api\Admin\UsersController@getLastRegUsers');
        Route::post('/adduser', 'Api\AuthController@createNewUser');
        Route::get('/issues', 'Api\Admin\UsersController@getIssues');
        Route::get('/users/{id}', 'Api\Admin\UsersController@getUserInfoById')->where(['id' => '[0-9]+']);
        Route::post('/add_user_cac/{id}', 'Api\Admin\UsersController@addUserCac')->where(['id' => '[0-9]+']);
        Route::get('/user_cltv/{id}', 'Api\Admin\UsersController@userCLTV');
        Route::get('/user_activity/{id}', 'Api\Admin\UsersController@getUserActivityById')->where(['id' => '[0-9]+']);
        Route::put('/users/{id}', 'Api\Admin\UsersController@editProfile')->where(['id' => '[0-9]+']);
        Route::post('/users/{id}', 'Api\Admin\UsersController@addBonusBandwidth')->where(['id' => '[0-9]+']);
        Route::get('/users/compliance/{user_id}', 'Api\Admin\UsersController@getUserComplianceById')->where(['user_id' => '[0-9]+']);
        Route::put('/users/compliance/{user_id}/{compliance_id}', 'Api\Admin\UsersController@unsuspendUser')
            ->where([
                'user_id' => '[0-9]+',
                'compliance_id' => '[0-9]+',
                ]);
        Route::post('/users/compliance/{user_id}', 'Api\Admin\UsersController@suspendUser')
            ->where([
                'user_id' => '[0-9]+',
            ]);
        Route::get('/location/{form}', 'Api\SearchController@getListLocation')->where(['form' => 'country|state|city']);
        Route::get('/users/{id}/status', 'Api\BillingController@sta tus')->where(['id' => '[0-9]+']);
        Route::post('/users/{id}/note', 'Api\Admin\UsersController@addUserNote')->where(['id' => '[0-9]+']);
        Route::get('/users/{id}/note', 'Api\Admin\UsersController@getUserNotes')->where(['id' => '[0-9]+']);
        Route::put('/users/{userId}/note/{noteId}', 'Api\Admin\UsersController@editUserNote')
            ->where([
                'userId' => '[0-9]+',
                'noteId' => '[0-9]+',
            ]);
        Route::delete('/users/{userId}/note/{noteId}', 'Api\Admin\UsersController@deleteUserNote')
            ->where([
                'userId' => '[0-9]+',
                'noteId' => '[0-9]+',
            ]);
        Route::get('/users/{id}/invoices', 'Api\BillingController@invoices')->where(['id' => '[0-9]+']);
        Route::get('/users/{id}/estimate', 'Api\BillingController@estimate')->where(['id' => '[0-9]+']);
        Route::get('/users/invoices/{invoice_id}/{user_id?}', 'Api\BillingController@getInvoiceDataById')->where([
            'invoice_id' => '[0-9]+',
            'user_id' => '[0-9]+',
        ]);
        Route::get('/users/{id}/usage', 'Api\BillingController@usage')->where(['id' => '[0-9]+']);
        Route::get('/users/{id}/information', 'Api\BillingController@information')->where(['id' => '[0-9]+']);
        Route::put('/users/{id}/information/general', 'Api\BillingController@updateGeneral')->where(['id' => '[0-9]+']);
        Route::get('/users/{id}/subscription', 'Api\AuthController@getSubscription')->where(['id' => '[0-9]+']);
        Route::post('/users/{id}/tags', 'Api\Admin\UsersController@addTagForUser')->where(['id' => '[0-9]+']);
        Route::post('/users/{id}/send-reset-password-link', 'Api\Admin\UsersController@sendResetPasswordLink')->where(['id' => '[0-9]+']);
        Route::post('invoice/{invoice_id}/users/{user_id}/pay', 'Api\BillingController@payInvoice')->where([
            'invoice_id' => '[0-9]+',
            'user_id' => '[0-9]+',
        ]);

        Route::get('/users/{user_id}/subscription/change', 'Api\Admin\SubscriptionController@index');
        Route::post('/users/{user_id}/subscription/recalc', 'Api\Admin\SubscriptionController@recalc');
        Route::put('/users/{user_id}/subscription/change', 'Api\Admin\SubscriptionController@changePlan');
        Route::post('/restore/user', 'Api\Admin\UsersController@restoreUser');
        Route::post('/users/get_plan_proportion/{id}', 'Api\AuthController@getAdminUserProration');
        Route::post('/users/update_subscription/{id}', 'Api\AuthController@updateUserSubscription');
        Route::post('/billing/make_card_primary/{id}/{user_id}', 'Api\Admin\UsersController@makeCardPrimary');
        Route::get('/billing/information/{id}', 'Api\BillingController@information');
        Route::post('/billing/information/general/{id}', 'Api\BillingController@updateGeneral');
        Route::get('/billing/get_user_cards/{id}', 'Api\Admin\UsersController@getUserCards');
    });


Route::group(['middleware' => ['cors'] ], function () {
    Route::post('/login', 'Api\AuthController@login');
    Route::post('/social_login', 'Api\AuthController@socialAuth');
    Route::post('/social_register', 'Api\AuthController@socialReg');
    Route::post('/comment_social_login', 'Api\AuthController@commentSocialReg');
    Route::post('/register', 'Api\AuthController@register');
    Route::post('/check-email', 'Api\AuthController@checkEmailExist');
    Route::post('/send-reset-password-link', 'Api\AuthController@sendResetPasswordLink');
    Route::post('/reset-password-by-token', 'Api\AuthController@setNewPasswordByToken');
    Route::post('/image/upload', ['as' => 'uploadImage', 'uses' => 'ImageController@store']);

    Route::post('/store_file', 'Api\VideoController@uploadSRTfile');
    Route::post('/store_subtitle_detail', 'Api\VideoController@store_subtitle_detail');
    Route::post('/translate_srt_file', 'Api\VideoController@translateSrt');
    Route::post('/reomveTranslatedSubtitle', 'Api\VideoController@reomveTranslatedSubtitle');
    Route::post('/reomveVideoSubtitle', 'Api\VideoController@reomveVideoSubtitle');
    Route::post('/editTranslatedSubtitle', 'Api\VideoController@editTranslatedSubtitle');
    Route::post('/uploadTranslatedSrtFileToEdit', 'Api\VideoController@uploadTranslatedSrtFileToEdit');
    Route::post('/generateAndDownloadSrt', 'Api\VideoController@generateAndDownloadSrt');
    Route::post('/generateSrtFromVideo', 'Api\VideoController@generateSrtFromVideo');
    Route::post('/checkStatus', 'Api\VideoController@checkStatus');
    Route::post('/store/fingerprints', 'Api\VideoController@fingerprintDetail');

    Route::post('/video/before_upload', 'Api\VideoController@before_upload');
    Route::post('/video/transcoding_progress_report', 'Api\VideoController@transcoding_progress_report');
    Route::post('/video/success', 'Api\VideoController@success');
    Route::post('/leadCapture', ['as' => 'leadCapture', 'uses' => 'Api\IntegrationsController@store']);
    Route::post('/video/bandwidth_report', 'Api\BunnyCDNController@bandwidth_report');
    Route::get('/video/bandwidth_report', 'Api\BunnyCDNController@bandwidth_report');
    Route::get('/playlist', 'Api\PlaylistController@show');

    Route::post('/removeVideoPixelRetargeting', 'Api\VideoController@removeVideoPixelRetargeting');

    Route::get('/cancelled_userinfo/{id}', 'Api\AuthController@cancelledUsers');
    Route::post('/pay_pending_invoice', 'Api\AuthController@payPendingInvoice');
    Route::post('/image/create-gif', 'ImageController@createGif');
    Route::post('/reset_user_password/{token}', 'Api\AuthController@resetUserPassword');
    Route::post('/get_partial_user/{id}', 'Api\AuthController@getPartialUser');
    Route::group(['prefix' => 'mailchimp'], function () {
        Route::get('get_members', 'Api\MailChimpController@getMembers');
        Route::post('addmember', 'Api\MailChimpController@addMember');
        Route::post('updatemember', 'Api\MailChimpController@updateMember');
    });
    Route::get('/get_plan_by_id/{id}', 'Api\AuthController@planById');
    Route::get('/remove_user/{id}', 'Api\AuthController@removeUser');
    Route::post('/paystickapi', 'Api\AuthController@payStickRegister');

});

    Route::get('/video/get_list', 'Api\VideoController@get_list');
    Route::get('/video/show', 'Api\VideoController@show');
    Route::get('record_usage', 'Api\RecordsOverUsage@recordOverUsage');
    // Analytics
    Route::post('/log', 'Api\StatisticsController@store');
    Route::post('/owner/status', 'Api\VideoController@ownerStatus');
    Route::get('/single-video-stats', 'Api\VideoController@singleVideoStats');
    Route::get('/get_user_info_by_id/{id}', 'Api\AuthController@getUserByID');
    Route::get('/get_user_info_by_cust_id/{id}', 'Api\AuthController@getUserByCUSTID');

    Route::group(['prefix' => 'stage-public/{stage_id}'], function () {
    Route::get('user', 'Api\StagesPublicController@getUserByStageId');
    Route::get('stages', 'Api\StagesPublicController@index');
    Route::get('stage-videos', 'Api\StagesPublicController@getVideosList');
    Route::get('stage-video-by-id', 'Api\StagesPublicController@getVideoById');
    Route::get('get-stage-video-by-id', 'Api\StagesPublicController@getVideoById');
    Route::post('editor/{id}/comments/{user_id}', 'Api\StagesPublicController@storeComment');
    Route::get('get-stage-playlist', 'Api\StagesPublicController@getStagePlayList');
    Route::get('get-stage-playlist-by-id', 'Api\StagesPublicController@getStagePlayListByid');
});


Route::group([
    'middleware' => ['cors', 'auth:api']
], function () {
    Route::post('/logout', 'Api\AuthController@logout');
    Route::post('/subscription', 'Api\AuthController@startFreeTrial');
    Route::put('/subscription', 'Api\AuthController@changeSubscription');
    Route::put('/subscription/to-annual', 'Api\AuthController@changeSubscriptionToAnnual');
    Route::get('/subscription', 'Api\AuthController@getSubscription');
    Route::get('/subscriptions', 'PlanController@getAllSubscriptions');
    Route::post('/subscription_tags', 'Api\MailChimpController@addMember');
    Route::get('/all-owner-videos', 'Api\VideoController@allOwnerVideos');
    Route::post('/restrict-videos', 'Api\VideoController@restrictVideos');
    Route::post('/owner-plan', 'Api\AuthController@ownerPlan');

    /**
     * User
     */
    Route::get('user', function () {
        return \App\User::getJavascriptObject();
    });

    /**
     * Permissions
     */
    Route::apiResource('permissions', 'Api\PermissionsController', ['names' => ['index' => 'permissions'], 'except' => ['store', 'show']]);

    Route::apiResource('stages', 'Api\StagesController', ['names' => ['index' => 'stages'], 'except' => ['store', 'show', 'destroy']]);

    Route::post('stages/about-info', 'Api\StagesController@updateAboutInfo')->name('stages.about-info');
    Route::post('stages/addpublic_playlist', 'Api\StagesController@managePublicPlaylist')->name('stages.addplaylist');

    Route::get('get-stage-videos', 'Api\StagesController@getVideosList');
    Route::get('get-stage-video-by-id', 'Api\StagesController@getVideoById');
    Route::get('get-stage-playlist', 'Api\StagesController@getStagePlayList');
    Route::get('get-stage-playlist-by-id', 'Api\StagesController@getStagePlayListByid');
    Route::post('get_proration', 'Api\AuthController@getProration');

    /**
     * Dashboard
     */
    Route::post('get-dashboard-statistics-data', 'Api\StatisticsController@getDashboardStatistics');
    Route::post('set-dashboard-settings', 'Api\SettingsController@updateDashboardSettings');

    Route::get('get-project-video-analytics', 'Api\StatisticsController@getStatisticsByProject');
    Route::post('get-project-video-analytics', 'Api\StatisticsController@getStatisticsByProject');
    Route::post('watch-session/realtime-users', 'Api\VideoController@realtimeUsers');

    /**
     * Projects
     */
    // Show all
    Route::get('projects', 'Api\ProjectController@index');
    // Show single
    Route::get('projects/show', 'Api\ProjectController@show')->middleware('projectAccess');
    // Create
    Route::post('projects', 'Api\ProjectController@store');
    // Update
    Route::put('projects', 'Api\ProjectController@update');
    // Delete
    Route::delete('projects', 'Api\ProjectController@destroy');
    Route::resource('projects', 'Api\ProjectController');
    Route::get('tutorial/video/{id}', 'Api\ProjectController@tutorialVideos');



    
    /**
     * Snaps
     */
    Route::resource('snaps', 'Api\SnapController');
    Route::post('snaps/store-shared', 'Api\SnapController@storeSharedSnaps');
    Route::post('snaps/labels', 'Api\SnapController@snapLabels');
    Route::post('snaps/update-shared', 'Api\SnapController@updateSharedSnaps');


    /**
     * Snap Page
     */
    Route::resource('snappage', 'Api\SnapPageController');
    //Route::post('snappage/related-snap-video', 'Api\SnapPageController@relatedSnapVideo');
    Route::post('snappage/filter-snap-page', 'Api\SnapPageController@filterSnapPage');
    Route::get('snappage/craete/{snapId}', 'Api\SnapPageController@createSnapPage');
    Route::post('snappage/filter-related-video', 'Api\SnapPageController@filterRelatedVideo');
    Route::post('snappage/duplicate', 'Api\SnapPageController@duplicate');
    Route::post('snappage/upload-image', 'Api\SnapPageController@uploadImage');
    Route::post('snappage/update-snap-page', 'Api\SnapPageController@updateSnapPage');

    /**
     * Shared Snaps
     */
    Route::get('shared-snaps', 'Api\SharedSnapsController@index');
    Route::get('shared-label-snaps/{label}', 'Api\SharedSnapsController@labelSnaps');
    Route::post('filter-shared-snaps', 'Api\SharedSnapsController@filterSharedSnaps');
    Route::post('filter-shared-snaps-video', 'Api\SharedSnapsController@filterSharedSnapVideo');


    /**
     * Playlist
     */
    Route::get('/playlists', 'Api\PlaylistController@index');
    Route::post('/playlist', 'Api\PlaylistController@store');
    Route::put('/playlist', 'Api\PlaylistController@save');
    Route::delete('/playlist', 'Api\PlaylistController@delete');
  

    
    // Thumbnails Experiment
    Route::post('projects/upload_thumbnail', 'Api\ExperimentController@upload_thumbnail');
    Route::post('projects/save_experiment', 'Api\ExperimentController@save_experiment');
    // Route::get('default_thumbnail', 'Api\ExperimentController@default_thumbnail');
    Route::post('add_experiment_clicks', 'Api\ExperimentController@add_experiment_clicks');
    Route::get('thumbnail-curved-images', 'Api\ExperimentController@curvedImages');
    Route::get('get-random-frames', 'Api\ExperimentController@randomFrames');
    Route::get('all-experiments', 'Api\ExperimentController@allExperiments');
    Route::post('experiment-title', 'Api\ExperimentController@experimentTitle');
    Route::post('get-file-details', 'Api\ExperimentController@getFileDetails');
    Route::post('thumbnail-details', 'Api\ExperimentController@thumbnailDetails');
    Route::post('experiment-actions', 'Api\ExperimentController@experimentActions');
    Route::post('projects/update-experiment', 'Api\ExperimentController@updateExperiment');
    Route::post('check-duration', 'Api\ExperimentController@checkDuration');
    Route::post('save-video-experiment', 'Api\ExperimentController@saveVideoExperiment');
    Route::post('validate-experiment-restart', 'Api\ExperimentController@canBeRestarted');
    Route::post('experiment-video-check', 'Api\ExperimentController@videoCheck');
    /**
     * Collaboration
     */
    Route::get(
        'project/{project_id}/collaborators', [
        'uses' => 'Api\CollaboratorsController@index',]);
    Route::post(
        'project/{project_id}/collaborators', [
        'uses' => 'Api\CollaboratorsController@store',]);
    Route::get(
        'project/{project_id}/collaborators/{collaborator_id}', [
        'uses' => 'Api\CollaboratorsController@show',]);
    Route::put(
        'project/{project_id}/collaborators/{collaborator_id}', [
        'uses' => 'Api\CollaboratorsController@update',]);
    Route::delete(
        'project/{project_id}/collaborators/{collaborator_id}', [
        'uses' => 'Api\CollaboratorsController@destroy',]);

    Route::get(
        'project/{project_id}/team_users', [
        'uses' => 'Api\CollaboratorsController@allTeamUsers',]);
    Route::put(
        'project/{project_id}/collaborators_access', [
        'uses' => 'Api\CollaboratorsController@setAccess',])->middleware('projectCollab');
    Route::get(
        'project/{project_id}/collaborators_access/check', [
        'uses' => 'Api\CollaboratorsController@checkAccess',]);

    /**
     * Project copy and move
     */
    Route::get(
        '/projects/except/{id}', [
        'uses' => 'Api\ProjectController@getProjectsExcept',]);
    Route::post(
        '/projects/move/video', [
        'uses' => 'Api\ProjectController@moveVideo',]);

    /**
     * Video
     */
    // get project videos
    Route::get(
        'project/{id}/videos', [
        'uses' => 'Api\ProjectController@getVideos']);
    // get all videos
    Route::get(
        'videos', [
        'uses' => 'Api\VideoController@all']);
    Route::post(
        'video', [
        'uses' => 'Api\VideoController@store']);
    Route::delete(
        'video', [
        'uses' => 'Api\VideoController@destroy']);
    Route::post(
        'video/duplicate', [
        'uses' => 'Api\VideoController@duplicate']);
    Route::post(
        'video/move', [
        'uses' => 'Api\VideoController@move']);

    Route::get('video/get_drm_keys', 'Api\VideoController@getDrmKeys');

    Route::match(
        ['get','post','head', 'patch'],
        'uploader/{project_id}/chunked/{upload_key?}',
        'Api\VideoController@chunkResumableUpload')
        ->name('upload.video');

    Route::post('uploader/{project_id}', 'Api\VideoController@xhrUpload');

    Route::match(
        ['get','post','head', 'patch'],
        'video-replace/{video_id}/chunked/{upload_key?}',
        'Api\VideoController@chunkResumableReplace')
        ->name('video.chunk.replace');

    /**
     * Video Editor
     */
    Route::get('editor', 'Api\EditorController@show');
    Route::delete('editor', 'Api\EditorController@destroy');
    Route::put('editor', 'Api\EditorController@update');
    Route::post('editor/video/duplicate', 'Api\EditorController@duplicate');
    Route::post('editor/video/thumbnail', 'Api\EditorController@createThumbnail');
    Route::post('editor/video/edit-title', 'Api\EditorController@editTitleAndDescription');
    Route::post('editor/video/copy-settings', 'Api\EditorController@copySettings');
    Route::post('editor/video/copy-video-to-project', 'Api\EditorController@copyVideoToProject');
    Route::post('editor/video/move-video-to-project', 'Api\EditorController@moveVideoToProject');
    Route::post('editor/get_all_videos_and_projects', 'Api\EditorController@getAllVideosAndProjects');
    Route::post('editor/delete-video', 'Api\EditorController@deleteVideo');
    Route::post('editor/branding-logo', 'Api\EditorController@brandingLogo');
      /**
     * Stripe 
     */
    Route::post('stripe_usage', 'Api\AuthController@createUsage');    


    /**
     * Search
     */
    Route::post(
        'search', [
        'uses' => 'Api\SearchController@index']);

    /**
     * Import
     */
    Route::get(
        'import/search', [
        'uses' => 'Api\ImportController@index']);
    Route::post(
        'import/search', [
        'uses' => 'Api\ImportController@index']);
    Route::post(
        'import/video', [
        'uses' => 'Api\VideoController@store']);

    /**
     * Integrations
     */
    Route::group(['prefix' => 'integrations'], function () {
        /**
         * Integrations custom routes
         */
        Route::get('/lists', 'Api\IntegrationsController@getLists')->name('integrations.lists');
        Route::post('/lists', 'Api\IntegrationsController@getLists')->name('integrations.lists');
        Route::get('/refresh', 'Api\IntegrationsController@refreshList')->name('integrations.refresh');
        Route::post('/refresh', 'Api\IntegrationsController@refreshList')->name('integrations.refresh');
    });

    /**
     * Integrations resource
     */
    Route::apiResource('integrations', 'Api\IntegrationsController', [
        'names' => ['index' => 'integrations'], 'except' => ['create', 'edit', 'show', 'update']
    ]);
    /**
     * Integrations authentication to connect.
     */
    /**
     * OAuth
     */

    Route::get('/oauth/aweber', 'Integrations\Aweber@connect')->name('integrations.aweber-connect');
    Route::get('/oauth/mailchimp', 'Integrations\MailChimp@connect')->name('integrations.mailchimp-connect');
    Route::get('/oauth/getresponse', 'Integrations\GetResponse@connect')->name('integrations.getresponse-connect');
    Route::get('/oauth/keap', 'Integrations\Keap@connect')->name('integrations.keap-connect');
    Route::get('/oauth/zoom', 'Integrations\Zoom@connect')->name('integrations.zoom-connect');
    Route::get('/oauth/zoho', 'Integrations\ZohoCRM@connect')->name('integrations.zoho-connect');
    Route::get('/oauth/gotowebinar', 'Integrations\GoToWebinar@connect')->name('integrations.gotowebinar-connect');
    

    /**
     * API Key
     */
    Route::post('/oauth/activecampaign', 'Integrations\ActiveCampaign@connect')->name('integrations.activecampaign-connect');
    Route::post('/oauth/convertkit', 'Integrations\ConvertKit@connect')->name('integrations.convertkit-connect');
    Route::post('/oauth/webinarjam', 'Integrations\WebinarJam@connect')->name('integrations.webinarjam-connect');
    Route::post('/oauth/hubspot', 'Integrations\HubSpot@connect')->name('integrations.hubspot-connect');
    Route::post('/oauth/zapier', 'Integrations\Zapier@connect')->name('integrations.zapier-connect');

    /**
     * Settings
     */
    Route::group(['prefix' => 'settings'], function () {
        /**
         * Account Information
         */
        Route::post('avatar-upload', 'Api\SettingsController@avatarUpload');
        Route::post('avatar-delete', 'Api\SettingsController@deleteProfileAvatar');
        Route::post('change-account-email', 'Api\SettingsController@changeAccountEmail');
        Route::post('update-profile-info', 'Api\SettingsController@updateProfileInfo');

        /**
         * Notification
         */
        Route::post('set-notification', 'Api\SettingsController@setNotification');

        /**
         * setGeolocationPrivacy
         */
        Route::post('set-geo-privacy', 'Api\SettingsController@setGeolocationPrivacy');
        Route::post('store-generat-setting', 'Api\SettingsController@storeGeneralSetting');

        /**
         * Stage
         */
        Route::post('stage-logo-upload', 'Api\SettingsController@stageLogoUpload');
        Route::post('set-stage-data', 'Api\SettingsController@setStageData');

        Route::post('save-sub-user-data', 'Api\SettingsController@saveSubUserData');
        Route::post('update-sub-user-data', 'Api\SettingsController@updateSubUserData');
        Route::post('delete-sub-user', 'Api\SettingsController@deleteSubUser');
        Route::post('disable-enable-sub-user', 'Api\SettingsController@disableEnableSubUser');

        Route::post('check-and-save-public-url', 'Api\SettingsController@checkUrlIsAvailableAndSave');
    });

    /**
     * Contacts
     */
    Route::group(['prefix' => 'contacts'], function () {
        Route::get('subscribers', 'Api\ContactsController@index');
        Route::post('delete-subscriber', 'Api\ContactsController@deleteSubscriber');
        Route::post('update-contact-info', 'Api\ContactsController@updateContactInfo');
        Route::get('get-all-videos', 'Api\ContactsController@getAllVideosByUserId');
        Route::post('get-filtered-contacts', 'Api\ContactsController@getFilteredContactsCount');
        Route::post('save-auto-tags', 'Api\ContactsController@saveAutoTagsData');
        Route::post('delete-auto-tag-condition', 'Api\ContactsController@deleteAutoTagCondition');
        Route::get('get-contact-aut-tags', 'Api\ContactsController@getContactsAutoTags');
        Route::post('update-active-status', 'Api\ContactsController@updateActiveAutoTag');
        Route::post('delete-auto-tag-data', 'Api\ContactsController@deleteAutoTagsData');
        Route::post('get-contact-watch-history', 'Api\ContactsController@getWatchedHistory');
        Route::post('get-contact-tag-history', 'Api\ContactsController@getTagHistory');
    });

    /**
     * Images
     */
    Route::get(
        '/images/all', [
        'as'   => 'getImages',
        'uses' => 'ImageController@index']);
    Route::delete(
        '/image', [
        'uses' => 'ImageController@destroy']);
    Route::put(
        '/image', [
        'uses' => 'ImageController@update']);

    /**
     * Analytics
     */
    Route::group(['prefix' => 'analytics'], function () {
        Route::get('sources', 'Api\StatisticsController@getSources');
        Route::post('statistics', 'Api\StatisticsController@index');
        Route::post('all_statistics', 'Api\StatisticsController@indexAllData');
    });

    /**
     * Comments
     */
    Route::get('editor/{id}/comments', [
        'uses' => 'Api\CommentController@index']);
    Route::get('editor/{id}/comments/{comment_id}', [
        'uses' => 'Api\CommentController@show']);
    Route::post('editor/{id}/comments', [
        'as'   => 'storeComment',
        'uses' => 'Api\CommentController@store']);
    Route::post('editor/{id}/comments/{comment_id}', [
        'uses' => 'Api\CommentController@update']);
    Route::delete('editor/{id}/comments/{comment_id}/delete', [
        'uses' => 'Api\CommentController@destroy']);

    Route::get(
        'plans', [
            'uses' => 'PlanController@all'
        ]
    );

    /**
     * Slates
     */
    Route::get('slates', [
        'uses' => 'SlateController@index']);
    Route::get('slates/templates', [
        'uses' => 'SlateController@templates']);
    Route::get('slates/{id}', [
        'uses' => 'SlateController@edit']);
    Route::post('slates', [
        'as'   => 'slates.store',
        'uses' => 'SlateController@store']);
    Route::put('slates', [
        'as'   => 'slates.update',
        'uses' => 'SlateController@update']);
    Route::delete('slates', [
        'as'   => 'slates.destroy',
        'uses' => 'SlateController@destroy']);


    /**
     * Billing
     */
    Route::group([
        'middleware' => ['cors', 'auth:api'],
        'prefix' => 'billing',
    ], function () {
        Route::post('/paypal/paid/trial', 'PlanController@getTrial');
        Route::post('/paypal/client-token', 'PlanController@getClientTokenForPayPal');
        Route::get('/status', 'Api\BillingController@status');
        Route::get('/invoices', 'Api\BillingController@invoices');
        Route::get('/get_invoice/{id}', 'Api\BillingController@getInvoiceById');
        Route::post('/invoice/{invoice_id}/pay', 'Api\BillingController@payInvoice')->where(['id' => '[0-9]+']);;
        Route::get('/estimate', 'Api\BillingController@estimate');
        Route::get('/usage', 'Api\BillingController@usage');

        Route::get('/information', 'Api\BillingController@information');
        Route::put('/information/general', 'Api\BillingController@updateGeneral');
        Route::put('/information/preferences', 'Api\BillingController@updatePreferences');
        Route::get('get_user_cards', 'Api\BillingController@getUserCards');
        Route::get('remove_card/{id}', 'Api\BillingController@removeCard');
        Route::get('make_card_primary/{id}', 'Api\BillingController@makeCardPrimary');
    });

    /**
     * Rooms
     */
    Route::get('/rooms/index', 'Api\RoomsController@index');
    Route::post('/rooms/delete/{id}', 'Api\RoomsController@delete');
    Route::get('/rooms/labels', 'Api\RoomsController@labels');
    Route::post('/rooms/save', 'Api\RoomsController@save');
    Route::post('/rooms/update/{id}', 'Api\RoomsController@update');
    Route::post('/rooms/duplicate', 'Api\RoomsController@duplicate');
    Route::post('/rooms/image-upload', 'ImageController@imageUpload');

    
    /**
     * Restrictions
     */
    Route::group(['prefix' => 'restrictions'], function () {
        Route::get('/index', 'RestrictionController@index')->name('restrictions.index');
    });

});

// No auth routes

// Snaps
Route::get('/project/{id}', 'Api\ProjectController@projectDetails');
Route::get('/shared/snap/{id}', 'Api\SnapController@snapDetails');
Route::post('/snaps/uploader/{project_id}', 'Api\VideoController@snapUpload');
Route::post('/snaps/delete-snap', 'Api\EditorController@deleteVideo');
Route::get('snappage/show/{id}', 'Api\SnapPageController@show');

// Zapier
Route::get('/zapier/leads', 'Integrations\Zapier@newLeads')->name('integration.zapier-leads');
Route::get('/zapier/videos', 'Integrations\Zapier@newVideos')->name('integration.zapier-leads');

// Videos
Route::post('watch-session/save', 'Api\VideoController@saveWatchSession');

// Rooms
Route::get('/rooms/{id}', 'Api\RoomsController@show');
Route::post('/rooms/add-views', 'Api\RoomsController@addViews');
Route::post('/rooms/video-details/{id}', 'Api\RoomsController@videoDetails');

Route::get('test-email', function(){
    $sharedSnap = SharedSnap::find('1');
    $video = Video::find('222');
    event( new SharedSnapRecorded($sharedSnap, $video));
});