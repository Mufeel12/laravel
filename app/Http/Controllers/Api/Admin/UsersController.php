<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddUserNote;
use App\Http\Requests\AddBonusWidthRequest;
use App\Http\Requests\AddUserTagRequest;
use App\Http\Requests\EditProfileRequest;
use App\Http\Requests\FilterUsersRequest;
use App\Http\Requests\SearchLastRegUsersRequest;
use App\Http\Requests\SuspendRequest;
use App\Notifications\ResetPasswordByAdmin;
use App\Statistic;
use App\Video;
use App\ActivityLog;
use App\BlockedEmail;
use App\Comments;
use App\Issue;
use App\Subscription;
use App\User;
use App\Card;
use Illuminate\Support\Facades\Password;
use Laravel\Spark\Spark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Helpers\StripeHelper;

class UsersController extends Controller
{
    public function getActiveUsers($status, $id = false, FilterUsersRequest $request)
    {
        $validatedData = $request->validated();
        return User::filterUsers($id, $validatedData, $status);
    }

    public function getLastRegUsers(SearchLastRegUsersRequest $request)
    {
        $validatedData = $request->validated();
        return User::getLastRegUsers($validatedData);
    }

    public function getUserComplianceById($id)
    {
        return User::getUserComplianceById($id);
    }

    public function addBonusBandwidth(AddBonusWidthRequest $request, $userId)
    {
        $validatedData = $request->validated();
        $user = User::where('id', $userId)->first();

        return $user->addBonusBandwidth($validatedData['bonus_bandwidth']);
    }

    public function getUserInfoById($id) {
        $user = User::where('id', $id)->first();

        if(!$user) {
            return abort(404, 'User not found');
        }

        $userDetails = User::getUserDetails($user);
        info(json_encode($userDetails));
        if(Subscription::where('user_id', $id)->first()) {
            $plan = $user->currentPlan()->first();
            
            $trial_days = config('services.subscription.trial_duration');
            $renewal = $plan->trial_ends_at > date('m-d-y', strtotime("+$trial_days days"))
                ? $plan->trial_ends_at
                : $plan->ends_at;
            $plan->ends_at = $renewal;
            $userDetails->subscription = $plan;
            $cycle = Spark::teamPlans()
                ->where('id', $plan->stripe_plan)
                ->first()
                ->interval;
            $userDetails->cycle = $cycle;
        }

        $cmd_str = 'curl -X POST ' . config('services.full_contact_api.url') . ' -H "Authorization: Bearer ' . config('services.full_contact_api.key') . '" -d "{\"email\": \"' . $user->email . '\"}"';
        exec($cmd_str, $output, $return_var);
        
        if ($return_var === 0) {
            $userDetails->full_contact = response()->json(json_decode($output[0]));
        } else { 
             $userDetails->full_contact = null;
              
        }
        $videos = $userDetails->top_videos;
        
        $top_videos = [];
        if($videos!=NULL && count($videos)>0)   {
            foreach($videos as $val){
               $top_videos[] = $this->getVideoObj($val, $user);
            }
        }
        
        $userDetails->top_videos = $top_videos;
        return $userDetails;
    }
    function getUserActivityById(Request $request,$id){
        $filter = json_decode($request->filter,true);
        $res = ActivityLog::where('user_id',$id);
        $res->where(function ($q) use ($filter) {
            if (count($filter) > 0) {
                foreach ($filter as $key => $val) {
                    if ($val) {
                        if ($key == 'login/out') {
                            $q->orWhereIn('activity_type', ['login', 'logout']);
                        } elseif ($key == 'project') {
                            $q->orWhereIn('activity_type', ['create_project', 'del_project']);
                        } elseif ($key == 'video') {
                            $q->orWhereIn('activity_type', ['video_upload', 'del_video']);
                        } elseif ($key == 'billing') {
                            $q->orWhereIn('activity_type', ['paid_invoice', 'payment_attempt']);
                        } elseif ($key == 'date') {
                            $dateOption = $filter['dateOption'];
                            if (isset($dateOption['greater'])) {
                                $date = date('Y-m-d', strtotime($dateOption['greater']));
                                $q->orWhereRaw("date(created_at) =?", $date);
                            }
                        }
                    }
        
                }
            }
        });
        $res = $res->orderBy('id','desc')->get();
        return $res;
        
    }
    public function addTagForUser($id, AddUserTagRequest $request)
    {
        $validatedData = $request->validated();
        return User::addTagForUser($id, $validatedData);
    }

    public function addUserNote(AddUserNote $request, $id)
    {
        $validatedData = $request->validated();

        $user = $request->user();
        return $user->addNote($id, $validatedData['note']);
    }

    public function getUserNotes($id)
    {
        $user = User::where('id', $id)->first();
        return $user->notes();
    }

    public function editUserNote(AddUserNote $request, $userId, $noteId)
    {
        $validatedData = $request->validated();
        $user = User::where('id', $userId)->first();

        return $user->editNote($noteId, $validatedData['note']);
    }

    public function deleteUserNote($userId, $noteId)
    {
        $user = User::where('id', $userId)->first();
        $user->deleteNote($noteId);

        return response('Note deleted', 200);
    }

    public function unsuspendUser($userId, $complianceId)
    {
        $user = User::where('id', $userId)->first();
        return $user->unsuspend($complianceId);
    }

    public function suspendUser(SuspendRequest $request, $userId)
    {
        $validatedData = $request->validated();
        $blockMail = isset($request->block_email) && $request->block_email ? $request->block_email : false;
        $user = User::where('id', $userId)->first();
        return $user->suspend($validatedData, $request->user(), $blockMail);
    }

    public function editProfile(EditProfileRequest $request, $userId)
    {
        $validatedData = $request->validated();
        $user = User::where('id', $userId)->first();

        return $user->editProfile($validatedData);
    }

    public function getIssues() {
        return Issue::get();
    }

     /**
    
    */
    private function getVideoObj($row, $user)
	{
		$video = Video::find($row->id);
		if (!$video->duration && ($video->duration_formatted === '' || is_null($video->duration_formatted))) {
			$result = shell_exec('ffmpeg -i ' . escapeshellcmd($video->path) . ' 2>&1');
			preg_match('/(?<=Duration: )(\d{2}:\d{2}:\d{2})\.\d{2}/', $result, $match);
			if (isset($match[1])) {
				$duration = $match[1];
				$video->duration_formatted = $match[1];

				$duration = explode(':', $duration);
				$duration = $duration[0] * 60 * 60 + $duration[1] * 60 + $duration[2];
				$duration = round($duration);
				$video->duration = $duration;

				$video->save();
			}
		}

		$obj = $row;
		$obj->full = $video->full();
		$obj->made_at = time_elapsed_string($video->created_at, false, $user->settings->timezone);
		$obj->published_on = date('M j, Y', strtotime($video->created_at));

		$owner = User::find($video->owner);
		$obj->owner_id = $owner->id;
		$obj->owner_name = $owner->name;
		$obj->owner_photo = $owner->photo_url;
		$obj->owner_logo = $owner->settings->logo;
		$obj->owner_stages = $owner->stages;
		$obj->comments = $video->comments()->whereNull('parent_id')->get()->map(function ($el) {
			$el = Comments::clearComments($el);

			$el->commented_at = date('M j, Y', strtotime($el->created_at));
			$el->showReplyInput = false;
			$el->showReply = false;

			$el->children->map(function ($cEl) {
				$cEl->commented_at = date('M j, Y', strtotime($cEl->created_at));

				return $cEl;
			});

			return $el;
		});

		return $obj;
	}

	public function sendResetPasswordLink($id)
    {
        $user = User::find($id);
        if ($user && !is_null($user)) {

            $token = Password::getRepository()->create($user);

            $user->notify(new ResetPasswordByAdmin($token, $user));

            return response()->json([
                'result' => 'success'
            ]);
        } else {
            return response()->json([
                'result'  => 'error',
                'message' => "We couldn't find this user"
            ]);
        }
    }

    public function restoreUser(Request $r)
    {
        $userId = $r->id;
        if ($userId) {
            $updated = User::find($userId)->update(
                [
                    'status_id'         => 1,
                    'billing_status'    => 'Cancelled'
                ]
            );
            if ($updated) {
                $userUpdated = $this->getUserInfoById($userId);
                Mail::send('/billing/restored', [
                    'full_name' => $userUpdated->name,    
                ], function ($m) use ($userUpdated) {
                    $m->to($userUpdated->email)->from('compliance@bigcommand.com', 'Bigcommand Compliance')->subject('Your Adilo account has been restored!');
                });
                BlockedEmail::where('email', $userUpdated->email)->delete();
                return response()->json([
                    'success' => 1,
                    'user'    => $userUpdated
                ]);
            }
        } else {
            return response()->json([
                'success' => 0
            ]);
        }
    }

    function addUserCac(Request $request,$id){
        $user = User::where('id',$id)->first();
        if($user){
            $user->user_cac = $request->input('user_cac');
            $user->save();
        }
    }
    function userCLTV(Request $request,$id){
        try{
        $stripe = new StripeHelper();
        $user = User::where('id',$id)->first();
        $cltv = $stripe->customerPaid($user->stripe_id);
        return response()->json($cltv);
        }catch (Exception $e) {
			return $e->getMessage();
		}
    }

    function makeCardPrimary(Request $request,$id,$user_id){
        $user = User::find($user_id);
        $card = Card::where(['user_id'=>$user->id,'id'=>$id])->first();
        if($card){ 
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            // $customer = \Stripe\Customer::retrieve($user->stripe_id);
            $customer = \Stripe\Customer::update($user->stripe_id,['default_source'=>$card->source_id]);
            Card::where(['user_id'=>$user->id])->update(['default_card'=>'no']);
            //Card::where(['user_id'=>$user->id,'id'=>$id])->update(['default_card'=>'yes']);
            $card->default_card = 'yes';
            $card->save();
            $user->exp_year=$card->exp_year;
            $user->exp_month=$card->exp_month;
            $user->card_brand=$card->card_brand;
            $user->card_last_four=$card->card_last_four;
            $user->save();
        return response()->json(['result'=>'success']);
        }
    }

    function getUserCards(Request $request,$id){
        $user = User::find($id);
        $res = Card::where('user_id',$user->id)->get();
        return response()->json($res);
    }
    
}