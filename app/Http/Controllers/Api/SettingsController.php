<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Integration;
use App\MailerLists;
use App\Notifications\PasswordChanged;
use App\Permission;
use App\Project;
use App\Stage;
use App\User;
use App\UserPermission;
use App\UserSettings;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Image;


class SettingsController extends Controller
{
    protected $hasher;

    public function __construct(HasherContract $hasher)
    {
        $this->hasher = $hasher;
    }

    public function updateDashboardSettings(Request $request)
    {
        $user = $request->user();

        $user->settings->dashboard_settings = json_encode($request->input('dashboard_settings'));
        $user->settings->save();

        return response()->json([
            'result' => 'success'
        ]);
    }

    /**
     * Upload New Avatar Of Profile
     * @param Request $request
     * @return JsonResponse
     */
    public function avatarUpload(Request $request)
    {
        $tempFile = $request->file('avatar');

        $extension = $tempFile->getClientOriginalExtension();

        $user = User::find($request->input('id'));

        $success = true;

        if ($user) {
            if ($user->photo_url != '' && !is_null($user->photo_url)) {
                $delete = Image::deleteImages($user, 'user-profile-image');
                $success = $delete['success'];
            }
            $tempFile = file_get_contents($tempFile);
            $tempSave   = Image::saveTempImage($tempFile, $user->id);
            if (!$tempSave['success']) return response($tempSave);
            $fileDetails = Image::getFileKey($tempSave['file_path'], $user->email, 'user-profile-image');
            $bucket_upload = Image::uploadImageToBucket($fileDetails['filekey'], $fileDetails['path'], $fileDetails['size']);

            if ($bucket_upload['success']) {
                $user->photo_url = $bucket_upload['file_path'];
                $user->save();

                $user->settings->user_image = $bucket_upload['file_path'];
                $user->settings->save();
                $user_image = $bucket_upload['file_path'];
            } else {
                $success = false;
                $user_image = $user->photo_url;
            }
        } else {
            $user_image = $user->photo_url;
        }
        Image::clearTemps('stage-logo', $user->id);
        if (!$success) return false;
        return response()->json([
            'result'     => $success ? 'success' : 'fail',
            'user_image' => $user_image
        ]);
    }

    /**
     * Resize .jpg Image
     *
     * @param $file
     * @param $w
     * @param $h
     * @return false|resource
     */
    private function resizeAvatarImage($file, $w, $h)
    {
        list($width, $height) = getimagesize($file);

        $src = imagecreatefromjpeg($file);
        $dst = imagecreatetruecolor($w, $h);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $width, $height);

        return $dst;
    }

    /**
     * Delete Profile Avatar
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteProfileAvatar(Request $request)
    {
        $user = User::find($request->input('id'));

        if ($user) {
            $user->photo_url = '';
            $user->save();

            $user->settings->user_image = '';
            $user->settings->save();

            $user = User::getUserDetails($user);
        }


        return response()->json([
            'result'    => 'success',
            'photo_url' => $user->photo_url
        ]);
    }

    /**
     * Update Account Email Address
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changeAccountEmail(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string|email|max:191|unique:users',
        ]);

        $user = User::find($request->input('id'));

        if ($user) {

            $user->email = $request->input('email');
            $user->billing_status = 'VerifyRequired';
            $user->facebook_id = null;
            $user->google_id = null;
            $user->twitter_id = null;
            $user->save();

        }

        return response()->json([
            'result'         => 'success',
            'email'          => $user->email,
            'billing_status' => $user->billing_status
        ]);
    }

    /**
     * Update Profile Information
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfileInfo(Request $request)
    {
//        return $request;

        $user = User::find($request->input('id'));

        if ($user) {
            $password = $request->input('password');
            $current_password = $request->input('current_password');
            if ($current_password != '' && !is_null($current_password) && $request->has('current_password')) {
                if ($password != '' && !is_null($password) && $request->has('password')) {
                    if ($this->hasher->check($current_password, $user->password)) {
                        $user->password = $this->hasher->make($password);
                        $user->notify(new PasswordChanged());
                    } else {
                        return response()->json('Invalid current password', 422);
                    }
                }
            }

            $user->name = $request->input('first_name') . ' ' . $request->input('last_name');
            $user->phone = $request->input('phone');
            $user->save();

            $user->settings->street_address = $request->input('street_address');
            $user->settings->apartment_suite = $request->input('apartment_suite');
            $user->settings->city = $request->input('city');
            $user->settings->state = $request->input('state');
            $user->settings->country = $request->input('country');
            $user->settings->zip_code = $request->input('zip_code');
            $user->settings->company = $request->input('company');
            $user->settings->timezone = $request->input('timezone');
            $user->settings->save();
            return  $user->settings;


        }

        return response()->json([
            'result' => 'success'
        ]);
    }

    /**
     * Set up settings notifications
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function setNotification(Request $request)
    {
        $user = $request->user();

        $user->settings->comments_my_video = $request->input('comments_my_video');
        $user->settings->shares_my_video = $request->input('shares_my_video');
        $user->settings->download_my_video = $request->input('download_my_video');
        $user->settings->email_captured = $request->input('email_captured');
        $user->settings->bandwidth_exceeded = $request->input('bandwidth_exceeded');
        $user->settings->save();

        return response()->json([
            'result' => 'success'
        ]);
    }

    /**
     * Brand Upload
     * @param Request $request
     * @return JsonResponse
     */
    public function stageLogoUpload(Request $request)
    {
        $tempFile = $request->file;
        $user = $request->user();
        $folder = $request->folder ?: "stage_logo";

        list($type, $tempFile) = explode(';', $tempFile);
        list(, $tempFile)      = explode(',', $tempFile);
        $tempFile = base64_decode($tempFile);

        if ($user) {
            try {
                $tempSave = Image::saveTempImage($tempFile, $user->id);

                if (!$tempSave['success']) return response($tempSave);
                $ownerFolder    = generate_owner_folder_name($user->email);
                $imageFilePath  = $tempSave['file_path'];
                Image::compressImage($tempSave['file_path'], $tempSave['file_path'], 75);
                $imageFileSize  = filesize($imageFilePath);
                $imageFileExt   = 'jpeg';
                $imageFileName  = str_random(32) . '.' . $imageFileExt;

                $fileKey        = "$ownerFolder/$folder/$imageFileName";
                $bucket_upload  = Image::uploadImageToBucket($fileKey, $imageFilePath, $imageFileSize);
    
                if ($bucket_upload['success']) {
                    $user->settings->logo = $bucket_upload['file_path'];
                    $user->settings->save();
                    Image::clearTemps('stage-logo', $user->id);
                    return response()->json([
                        'success' => true,
                        'logo'   => $bucket_upload['file_path']
                    ]);
                }
            } catch (\Exception $e) {
                Image::clearTemps('stage-logo', $user->id);
                return response()->json(['success' => false]);
            }
        }
        Image::clearTemps('stage-logo', $user->id);
        return response()->json(['success' => false]);
    }

    /**
     * Set user stage information
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function setStageData(Request $request)
    {
        $user = $request->user();
        $result = 'false';
        $code = 403;
        $message = "You haven't permission to edit stage";

        foreach ($user->user_permissions as $item){
            if ($item->permission == 'edit-stage'){
                $code = 400;
                $message = "Bad request";

                $team = $user->currentTeam();
                $owner_id = $team->owner_id;
                $settings = UserSettings::where(['user_id' => $owner_id])->first();

                $settings->stage_visibility = $request->input('stage_visibility');
                $settings->show_email_capture = $request->input('show_email_capture');
                $settings->notify_to_subscribers = $request->input('notify_to_subscribers');
                $settings->auto_tag_email_list = $request->input('auto_tag_email_list');
                $settings->stage_tags = json_encode($request->input('stage_tags'));
                $settings->stage_name = $request->input('stage_name');

                if ($request->has('email_list_id') && !is_null($request->input('email_list_id')) && !empty($request->input('email_list_id'))) {
                    $email_list_id = $request->input('email_list_id');
                    $settings->email_list_id = $email_list_id;
                    $service_key = explode('_', $email_list_id);
                    if ($service_key) {
                        $owner = User::where(['id' => $owner_id])->with(['integrations' => function($q) use($service_key){
                            $q->where(['service_key' => $service_key[0]]);
                        }])->first();

                        if (is_array($owner->integrations) && !empty($owner->integrations)) {
                            $settings->integration_id = $owner->integrations->id;
                        }
                    }
                }

                User::saveDashboardSetting("2");
                if ($settings->save()){
                    $code = 200;
                    $result = 'success';
                    $message = 'Stage data successfully saved';
                }
            }
        }

        return response()->json([
            'result' => $result,
            'message' => $message
        ], $code);
    }

    /**
     * Create new sub user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function saveSubUserData(Request $request)
    {
        $owner = $request->user();

        $this->validate($request, [
            'name'     => 'required|string|max:191',
            'email'    => 'required|string|email|max:191|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = new User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = $this->hasher->make($request->input('password'));
        $user->trial_ends_at = now($owner->settings->timezone)->addDays(config('services.subscription.trial_duration'));
        $user->billing_status = 'Active';
        $user->last_activity = now($owner->settings->timezone);
        $user->user_status = 1;
        $user->status_id = 1;
        $user->current_team_id = $owner->currentTeam()->id;
        $user->save();

        User::saveDashboardSetting("3");

        UserPermission::createDefaultUserPermissions($user, 'subuser');

        $assigned = $request->input('assigned_permissions');
        $this->setSubUserPermission($user, $assigned);
        if ($owner->currentTeam) {
            $owner->currentTeam->users()->attach($user, ['role' => 'subuser']);
        }
        addToLog(['user_id'=>$request->user()->id,
			'activity_type'=>'subuser_added',
			'subject'=>"Added new sub-user: <span class='activity-content'>$user->name</span>"
			]);
        return response()->json([
            'result' => 'success'
        ], 200);
    }

    /**
     * Set sub user permission
     * @param $user
     * @param $assigned
     */
    private function setSubUserPermission($user, $assigned)
    {
        if (count($assigned) > 0) {
            $user_perms = $user->permissions()->get();
            foreach ($user_perms as $row) {
                $u_perm = UserPermission::where('user_id', $user->id)->where('permission_id', $row->id)->first();
                $checked = false;
                for ($i = 0; $i < count($assigned); $i++) {
                    if ($row->id == $assigned[$i]) {
                        $u_perm->permission = 1;
                        $u_perm->save();
                        $checked = true;
                        break;
                    }
                }

                if (!$checked) {
                    $u_perm->permission = 0;
                    $u_perm->save();
                }
            }
        }
    }

    /**
     * Update sub user
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSubUserData(Request $request)
    {
        $id = $request->input('id');
        $this->validate($request, [
            'name'  => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,' . $id,
        ]);

        $user = User::find($id);
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->last_activity = now($user->settings->timezone);
        if ($request->has('password')) {
            if (!is_null($request->input('password')) && $request->input('password') != '') {
                $user->password = $this->hasher->make($request->input('password'));
            }
        }

        User::saveDashboardSetting("3");
        $user->save();

        $assigned = $request->input('assigned_permissions');
        $this->setSubUserPermission($user, $assigned);

        return response()->json([
            'result' => 'success'
        ], 200);
    }

    /**
     * Delete Sub user
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteSubUser(Request $request)
    {
        $id = $request->input('id');

        $user = User::find($id);
        $useremail = $user->email;
        $user->delete();

        DB::table('team_users')->where('user_id', $id)->delete();

        DB::table('user_permissions')->where('user_id', $id)->delete();

        Project::where('owner', $id)->update([
            'owner' => $request->user()->id
        ]);

        Integration::where('user_id', $id)->update([
            'user_id' => $request->user()->id
        ]);

        DB::table('subscribers')->where('user_id', $id)->update([
            'user_id' => $request->user()->id
        ]);
        addToLog(['user_id'=>$request->user()->id,
			'activity_type'=>'subuser_del',
			'subject'=>"Deleted sub-user: <span class='activity-content'>$useremail</span>"
			]);
        return response()->json([
            'result' => 'success'
        ], 200);
    }

    /**
     * Delete Sub user
     * @param Request $request
     * @return JsonResponse
     */
    public function disableEnableSubUser(Request $request)
    {
        $id = $request->input('id');

        $user = User::find($id);
        $user->user_status = $request->input('user_status');
        $user->save();

        return response()->json([
            'result' => 'success'
        ], 200);
    }


    /**
     * Check url is available and save it
     * @param Request $request
     * @return JsonResponse
     */
    public function checkUrlIsAvailableAndSave(Request $request)
    {
        $status_code = 400;
        $user = User::getUserDetails(auth()->user());
        $stage_permission = Permission::where(['permission' => 'edit-stage'])->first();
        $can_edit = $user->user_permissions()->where(['user_id' => auth()->user()->id, 'permission_id' => $stage_permission->id])->first();

        if ($can_edit == null) {
            $status_code = 403;
            $result = 'Access forbidden';
        } else {
            $url = $request->input('url');
            $action = $request->input('action');
            $availableUrl = UserSettings::where(['stage_public_url' => $url])->first();
            $result = $availableUrl != null ? ($user->owner->settings->stage_public_url == $availableUrl->stage_public_url ? 'success' : 'fail') : 'success';
            $status_code = 200;
            if ($result == 'success') {
                
                if ($action == 'save'){
                    $user->owner->settings->stage_public_url = $url;
                    $user->owner->settings->save();
                }
            }

        }

        return response()->json([
            'result' => $result,
            'referrer' => $request->fullUrl()
        ], $status_code);
    }

    /**
     * set geolocation privacy for user account
     * @param Request $request
     * @return JsonResponse
     */
    public function setGeolocationPrivacy(Request $request)
    {
        $user = $request->user();

        if($user->settings->restricted_countries == null){
            $request_data[$request->country_type] = $request->restricted_countries;


        }else{
            $request_data = json_decode($user->settings->restricted_countries, true);
            $request_data[$request->country_type] = $request->restricted_countries;
//            dd($request_data);
        }
        $user->settings->restricted_countries = json_encode($request_data);
        $user->settings->save();

//        dd(json_encode($request_data));
        return response()->json([
            'result' => 'success'
        ]);
    }
    public function storeGeneralSetting(Request $request)
    {
        $user = $request->user();

//        dd($user->settings->resume_player, $request->resume_player);
        $user->settings->resume_player = $request->resume_player;
        $user->settings->pause_player = $request->pause_player;
        $user->settings->sticky_player = $request->sticky_player;
        $user->settings->autoplay = $request->autoplay;
        $user->settings->default_resolution = $request->default_resolution;
        $user->settings->save();

//        dd(json_encode($request_data));
        return response()->json([
            'result' => 'success'
        ]);
    }

}
