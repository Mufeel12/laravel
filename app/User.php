<?php

namespace App;

use App\Notifications\AccountDeleted;
use App\Notifications\ResetPasswordLinkSent;
use Braintree_Gateway;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\HasApiTokens;
use Laravel\Spark\CanJoinTeams;
use Laravel\Spark\LocalInvoice as Invoice;
use Laravel\Spark\User as SparkUser;
use Laravel\Spark\Spark;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use function Aws\filter;
use Illuminate\Support\Facades\Log;
use Stripe\InvoiceItem as StripeInvoiceItem;
use App\BlockedEmail;
use App\FailedPayment;
use App\Subscriber;
use App\Project;
use App\SignupCoupon;

class User extends SparkUser
{
    use CanJoinTeams, HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'billing_address',
        'billing_address_line_2',
        'billing_country',
        'billing_city',
        'billing_zip',
        'phone',
        'payment_method',
        'billing_status',
        'last_activity',
        'login_country',
        'login_city',
        'updated_at',
        'status_id',
        'billing_type',
        'user_origin',
        'admin_user_status',
        'user_token',
        'user_plan_id',
        'is_discount_applied',
        'referral_source',
        'signup_source',
        'forensic_watermarking'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'card_country',
        'billing_address',
        'billing_address_line_2',
        'billing_city',
        'billing_zip',
        'billing_country',
        'billing_state',
        'extra_billing_information',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
        'uses_two_factor_auth' => 'boolean',
    ];
    protected $with =['suspended'];

    /**
     * Model boot
     */
    public static function boot()
    {
        parent::boot();

        static::created(function ($user) {

            $initials = self::getInitials($user);

            UserSettings::createDefaultSettings($user);
        });

        static::deleting(function ($user) {
            $settings = $user->settings;
            $settings->delete();
        });
    }

    /**
     * Returns initials of user
     *
     * @param $user
     * @return string
     */
    public static function getInitials($user)
    {
        // Get initials
        $initials = '';
        if ($user->first_name) {
            $initials .= substr($user->first_name, 0, 1);
            if ($user->last_name) {
                $initials .= substr($user->last_name, 0, 1);
            }
        } else if ($user->email) {
            $initials = substr($user->email, 0, 1);
        }

        return strtoupper($initials);
    }

    /**
     * Returns free space of user in bytes
     *
     * @param bool $userId
     * @return int
     */
    public static function getFreeSpace($userId = false)
    {
        if ($userId === false) {
            $userId = Auth::id();
        }

        return VideoFileStorage::getFreeSpace($userId);
    }

    /**
     * Returns a user object for Javascript use
     *
     * @return mixed
     */
    public static function getJavascriptObject()
    {
        $user = Auth::user();

        return self::getUserDetails($user);
    }

    public static function getUserDetails($user)
    {

        if (!$user) return $user;

        $user_row = $user;
        info(json_encode($user));
        $user->mertered_cycle = null;
        if ($user->currentPlan) {
            $end_cycle = $user->currentPlan->ends_at;
            $mertered_cycle = $user->currentPlan->ends_at;
            $plan = Spark::allPlans()->where('id', $user->currentPlan->stripe_plan)->first();
            $user->bandwidth_limit = $plan ? $plan->attributes['bandwidth_limit'] : null;
            $user->videos_limit = $plan ? $plan->attributes['videos_limit'] : null;
            $trial_ends_at = date('Y-m-d',strtotime($user->currentPlan->trial_ends_at));
            $ends_at = date('Y-m-d',strtotime($user->currentPlan->ends_at));
            $created_at = date('Y-m-d',strtotime($user->currentPlan->created_at));
            $date = date('Y-m-d');
            if($trial_ends_at>=$date){
                $end_cycle = $trial_ends_at;
                $mertered_cycle = $trial_ends_at;
            }else{
                if($ends_at>=$date){
                    $end_cycle = $ends_at;
                }else{
                $day = date('d',strtotime('+1 days '.$ends_at));
                if($day>=date('d')){
                    $end_cycle = date("Y-m-$day"); 
                }else{
                    $end_cycle = date("Y-m-$day");
                    $end_cycle = date('Y-m-d',strtotime('+1 months '.$end_cycle));
                }
            } 
            $mday = date('d',strtotime($created_at));
            if($mday<date('d')){
                $mertered_cycle = date('Y-m-d',strtotime('+1 month '.$created_at));
            }else{
                $mertered_cycle = date("Y-m-$mday");
            }
             
            }
            if($user->currentPlan->stripe_plan=='free'){
               $end_cycle = $user_row->calculatenedatforfreeplan($user->currentPlan->updated_at,$user->currentPlan->ends_at);
               $mertered_cycle = $mertered_cycle;
                
            }
            $user->ends_at = $end_cycle;
            $user->mertered_cycle = $mertered_cycle;
            $stripePlan = SignupCoupon::where(['plan_id'=>$plan->name,'status'=>'active'])->first();
            $user->discount = (isset($stripePlan->amount)?$stripePlan->amount:0);

            // Should be here
            if(strpos($user->currentPlan->stripe_plan, 'paykickstart') !== false){
                $user->mertered_cycle = null;
            }
        }
        // Was here
        $user->site_domain = config('services.site_domain.url');
        if (!$user->settings) {
            $user->settings = UserSettings::createDefaultSettings($user);
        }

        $currentTeam = $user_row->currentTeam();
        $user->sub_users = 0;
        $user->currentTeam = $currentTeam ? self::reSetCurrentTeam($currentTeam) : $currentTeam;
        if ($currentTeam->owner_id === $user_row->id) {
            $user->sub_users = DB::table('team_users')
                ->where('team_id', $currentTeam->id)
                ->where('role', 'subuser')
                ->where('user_id', '<>', $user_row->id)
                ->count();
        }

        $team_user_ids = DB::table('team_users')->where('team_id', $currentTeam->id)->pluck('user_id');
        $team_videos_count = Video::whereIn('owner', $team_user_ids)->count();
        $user->team_has_videos = $team_videos_count > 0 ? true : false;
        
        if (!$user->user_permissions || count($user->user_permissions) == 0) {
            UserPermission::createDefaultUserPermissions($user, $currentTeam->pivot->role);
        }

        $user->freeSpace = $user_row->getFreeSpace($user->id);
        $user->last_login = $user_row->last_login;
        $user->member_since = $user_row->member_since;
        $user->last_invoice = Invoice::where('user_id',$user_row->id)->orderBy('created_at', 'desc')->first();
        $user->project_counts = Project::where('owner',$user_row->id)->count();
        $user->settings->avatar = $user_row->settings->avatar;
        $user->settings->dashboard_settings = is_null($user_row->settings->dashboard_settings) ? [] : json_decode($user_row->settings->dashboard_settings);
        $user->settings->restricted_countries = is_null($user_row->settings->restricted_countries) ? [] : json_decode($user_row->settings->restricted_countries);
        $user->settings->autoplay = $user_row->settings->autoplay == 1 ? true : false ;
        $user->settings->resume_player = $user_row->settings->resume_player == 1 ? true : false ;
        $user->settings->pause_player = $user_row->settings->pause_player == 1 ? true : false ;
        $user->settings->sticky_player = $user_row->settings->sticky_player == 1 ? true : false ;
        $user->full_name = $user_row->full_name;
        $user->first_name = $user_row->first_name;
        $user->last_name = $user_row->last_name;
        $user->bandwidth_cycle = AccountSummary::where('user_id', $user->id)->first()->bandwidth_usage;
        $user->videos_count = Video::where('owner', $user->id)->count();
        $user->failed_payment = FailedPayment::with('invoice')->where(['user_id'=>$user_row->id])->first();
        if($user->failed_payment){
        $user->failed_payment->plan = Spark::teamPlans()->where('id', $user_row->currentPlan->stripe_plan)->first();
        }
        
        $plan_name = Subscription::where('user_id', $user->id)->first();
        if ($plan_name != null) {
            $user->plan_name = $plan_name->name;
        }
        $user->space_usage = $user_row->getUserSpaceUsage($user->id);
        $user_usage = $user_row->getUserUsage($user);
        if ($user_usage != null) {
            $user->anti_piracy = ($user_usage->anti_piracy != null) ? $user_usage->anti_piracy : 0;
            $user->forensic_sessions_count = ($user_usage->forensic_sessions_count != null) ? $user_usage->forensic_sessions_count : 0;
            $user->visual_sessions_count = ($user_usage->visual_sessions_count != null) ? $user_usage->visual_sessions_count : 0;

            $caption_minutes = ($user_usage->caption_minutes != null) ? $user_usage->caption_minutes : 0;
            $user->caption_minutes = $caption_minutes;
            $translation_minutes = ($user_usage->translation_minutes != null) ? $user_usage->translation_minutes : 0;
            $user->translation_minutes = $translation_minutes;
        }


        $user->enrich = $user_row->getEnrichement($user);
        $user->contacts = Subscriber::where('user_id',$user_row->id)->count();
        $user->enrich_max = 0;
        $user->translation_max = 0;
        $user->anti_max = 0;
        $user->forensic_max = 0;
        $user->dynamic_visual_max = 0;
        $user->caption_minute_max = 0;
        $user->max_storage = 'UNLIMITED';
        if ($user->currentPlan != null) {
            if ($user->currentPlan->stripe_plan == 'marketer-paykickstart-static') {
                $user->max_storage = '25 GB';
                
            }
            if ($user->currentPlan->stripe_plan == 'commercial-paykickstart-static') {
                $user->max_storage = '50 GB';

            }
            if ($user->currentPlan->stripe_plan == 'personal-paykickstart-static') {
                $user->max_storage = '10 GB';

            }
            if ($user->currentPlan->stripe_plan == 'elite-paykickstart-static') {
                $user->max_storage = '250 GB';

            }
            if ($user->currentPlan->stripe_plan == 'starter-monthly-static' || $user->currentPlan->stripe_plan == 'starter-annual-static') {
                $user->enrich_max = 100;
                $user->translation_max = 100;
                $user->anti_max = 0;
                $user->forensic_max = 0;
                $user->dynamic_visual_max = 0;
                $user->caption_minute_max = 100;
            } else if ($user->currentPlan->stripe_plan == 'pro-monthly-static' || $user->currentPlan->stripe_plan == 'pro-annual-static' || $user->currentPlan->stripe_plan == 'pro-semi-bundle-static' || $user->currentPlan->stripe_plan == 'pro-quaterly-bundle-static' || $user->currentPlan->stripe_plan == 'pro-annual-bundle-static') {
                $user->enrich_max = 250;
                $user->translation_max = 300;
                $user->anti_max = 2000;
                $user->forensic_max = 2000;
                $user->dynamic_visual_max = 5000;
                $user->caption_minute_max = 300;
            } else if ($user->currentPlan->stripe_plan == 'business-monthly-static' || $user->currentPlan->stripe_plan == 'business-annual-static' || $user->currentPlan->stripe_plan == 'business-semi-bundle-static' || $user->currentPlan->stripe_plan == 'business-quaterly-bundle-static' || $user->currentPlan->stripe_plan == 'business-annual-bundle-static') {
                $user->enrich_max = 800;
                $user->translation_max = 1000;
                $user->anti_max = 5000;
                $user->forensic_max = 5000;
                $user->dynamic_visual_max = 10000;
                $user->caption_minute_max = 1000;
            }
        }
        $date = date('Y-m-d');

        $user->nexCycleDate = $user_row->getCycleDate($user);

        $now = strtotime($date);
        $datediff = strtotime($user->nexCycleDate) - $now;
        $user->remainingDays = round($datediff / (60 * 60 * 24));
        $user->total_contact = Subscriber::where('user_id', $user_row->id)->count();
        $user->projects = Project::where('owner', $user_row->id)->count();

        $user->bonus_bandwidth = $user_row->settings->bonus_bandwidth;
        $user->total_views = 0;
        //$user->total_watch_time = AccountSummary::where('user_id', $user->id)->first()->views_total_watch_time;
        $videosId = DB::table('videos')->where('owner', $user->id)->pluck('id');
         
        $user->total_comments = DB::table('comments')->whereIn('commentable_id',$videosId)->count();
        $total_start = DB::table('statistics')->where('statistics.user_id', $user->id)
            ->where('statistics.domain', config('app.site_domain'))
            ->where('statistics.watch_end', '<>', '0')
            ->where('statistics.event', 'video_view')
            ->sum('watch_end');
        $total_end = DB::table('statistics')->where('statistics.user_id', $user->id)
            ->where('statistics.domain', config('app.site_domain'))
            ->where('statistics.watch_end', '<>', '0')
            ->where('statistics.event', 'video_view')
            ->sum('watch_start');
        $user->total_watch_time = abs($total_end - $total_start);

        $user->top_videos = Video::select('videos.*')->with(['view' => function ($q) {
            $q->where(function ($q) {
                $q->where('statistics.watch_start', '<>', '0')
                    ->orWhere('statistics.watch_end', '<>', '0');
            })
                ->where('statistics.watch_end', '<>', '0')
                ->where('statistics.event', 'video_view')
                ->where('statistics.domain', config('app.site_domain'))
                ->groupBy('statistics.watch_session_id')
                ->groupBy('statistics.video_id');
        }])
            ->join('statistics', 'statistics.video_id', '=', 'videos.id')->where('owner', $user_row->id)
            ->groupBy('statistics.video_id')
            ->orderBy(DB::raw('COUNT(statistics.video_id)'), 'desc')
            ->get();

        //$user->storage = $user_row->storageSize();
        $user->top_videos->map(function ($row) use ($user) {
            $row->view_count = count($row->view);
            $user->total_views += $row->view_count;
            return $row;
        });
        $user->view_count = $user->total_views;
        if (!$user->stages) {
            $user->stages = Stage::createDefaultStage($user_row);
        } else {
            $user->stages = $user_row->stages;
        }

        $user->owner = $currentTeam->owner;
        $user->owner->settings = $currentTeam->owner->settings;
        $user->owner->stages = $currentTeam->owner->stages;
        $user->owner_user = User::where('id', $currentTeam->owner_id)->first();

        $state_tags = UserSettings::where(['user_id' => $user->owner->id])->pluck('stage_tags');
        $user->settings->stage_tags = $state_tags[0];

        $email_list_id = UserSettings::where(['user_id' => $user->owner->id])->pluck('email_list_id');
        $user->settings->email_list_id = $email_list_id[0];
        // $user->activity = ActivityLog::where('user_id',$user_row->id)->get();


        $user->current_plan = Subscription::where('user_id', $user->id)->first();

        /** */
        $user_costs = 0;
        $encodingcost = Video::select(DB::raw('SUM(drm_sessions_count) as anti_piracy,SUM(forensic_sessions_count) as forensic_sessions_count,SUM(visual_sessions_count) as visual_sessions_count,SUM(caption_minutes) as caption_minutes,SUM(translation_minutes) as translation_minutes,(SUM(server_cost_max)+SUM(transfer_cost) ) as encoding_cost'))->where('owner', $user->id)->first();
        $user->encodingcost = isset($encodingcost->encoding_cost)?number_format($encodingcost->encoding_cost,6):0;
        //space usage cost
        $perGB = 0.006;
        $perMB = 0.006/1024;
        $perKBCost = $perMB/1024;
        $total_usage = $user->space_usage;
        $total_usage_in_kb = $user_row->getSizeInGB($total_usage);
        $space_usage_cost = $total_usage_in_kb*$perGB;
        $user->space_usage_cost = $user_row->formatNumber($space_usage_cost);
       

        // bandwith cost 
        $allVideos = Video::where('owner',$user_row->id)->get()->toArray();
        $allVideoIds = array_column($allVideos,'video_id');
        $bandwidthCost = DB::table('bunnycdn_bandwidth_records')->whereIn('video_id',$allVideoIds)->selectRaw('(SUM(bytes_sent)) as bytes_sent')->first();
        $bytes_sent = isset($bandwidthCost->bytes_sent)?$bandwidthCost->bytes_sent:0;
        $perGbBandWidthCost = 0.005;
        $perMBBandWidthCost = 0.005/1024;
        $bandWidthInGb = $user_row->getSizeInGB($bytes_sent);
        $band_width_cost = $bandWidthInGb*$perGbBandWidthCost;
        $user->band_width_cost = $user_row->formatNumber($band_width_cost);
        

        //anti priracy cost
        $antipiracyUsage = $encodingcost->anti_piracy;
        $chargePerAntipiracy = 0.005;
        $antiracyCharge = $chargePerAntipiracy*(float)$antipiracyUsage;
        $user->antiracyCharge = $user_row->formatNumber($antiracyCharge);
        // forensic watermark sessions
        $forensicUsage = $encodingcost->forensic_sessions_count;
        $chargePerforensic = 0.002;
        $forensicCharge = $chargePerforensic*(float)$forensicUsage;
        $user->forensicCharge = $user_row->formatNumber($forensicCharge);
        // visual_sessions_count watermark sessions
        $visualUsage = $encodingcost->visual_sessions_count;
        $chargePervisual = 0.001;
        $uservisualCharge = $chargePervisual*(float)$visualUsage;
        $user->visualCharge = $user_row->formatNumber($uservisualCharge);
        
        // caption_minutes 
        $caption_minutesUsage = $encodingcost->caption_minutes;
        $chargePercaption_minutes = 0.025;
        $caption_minutesCharge = $chargePercaption_minutes*(float)$caption_minutesUsage;
        $user->caption_minutesCharge = $user_row->formatNumber($caption_minutesCharge);
       
        // translation_minutes 
        $translation_minutesUsage = $encodingcost->translation_minutes;
        $chargePertranslation_minutes = 0.025;
        $translation_minutesCharge = $chargePertranslation_minutes*(float)$translation_minutesUsage;
        $user->translation_minutesCharge = $user_row->formatNumber($translation_minutesCharge);
  
        // translation_minutes 
        $translation_minutesUsage = $encodingcost->translation_minutes;
        $chargePertranslation_minutes = 0.025;
        $translation_minutesCharge = $chargePertranslation_minutes*(float)$translation_minutesUsage;
        $user->translation_minutesCharge = $user_row->formatNumber($translation_minutesCharge);
        //enrich
        $perEnrichCost = 0.035;
        $user->enrich_cost = (float)$user->contacts*$perEnrichCost;
        $totalCs = $user->encodingcost + $user->space_usage_cost + $user->band_width_cost + $user->antiracyCharge + $user->forensicCharge + $user->visualCharge  + $user->caption_minutesCharge  + $user->translation_minutesCharge;  
        $user->totalCs = $user_row->formatNumber($totalCs);
        $user->cards = $user_row->userCards;
        /** */
        unset($user->teams);
        $user->now = \Carbon\Carbon::now();
        return $user;
    }
    function formatNumber($num){
        return ($num>0)?round((float)$num,4):0;
         
    }
    function getSizeInGB($bytes){
        return (float)$bytes/(1024*1024*1024);
    }
    public function storageSize()
    {
        $disk = Storage::disk('wasabi');
        $files = $disk->allFiles($this->email);

        $size = array_reduce($files, function ($acc, $file) use ($disk) {
            return $acc + $disk->size($file);
        });

        return array_pop($size);
    }

    /**
     * Reset format current team data per user
     *
     * @param $currentTeam
     * @return array
     */
    private static function reSetCurrentTeam($currentTeam)
    {
        $currentTeamUsers = [];
        foreach ($currentTeam->users as $key => $row) {

            $currentTeamUsers[$key] = $row;
            if (!$row->settings) {
                $row->settings = UserSettings::createDefaultSettings($row);
            }

            if (!$row->user_permissions || count($row->user_permissions) == 0) {
                UserPermission::createDefaultUserPermissions($row, $currentTeam->pivot->role);
            }

            $currentTeamUsers[$key]->settings->avatar = $row->settings->avatar;
            $currentTeamUsers[$key]->full_name = $row->full_name;
            $currentTeamUsers[$key]->first_name = $row->first_name;
            $currentTeamUsers[$key]->last_name = $row->last_name;
            $currentTeamUsers[$key]->company = config('app.name') . ' Inc';
            $currentTeamUsers[$key]->freeSpace = $row->getFreeSpace($row->id);
            $currentTeamUsers[$key]['last_signin'] = time_elapsed_string($row->last_activity, false, $row->settings->timezone);
            $currentTeamUsers[$key]['member_since'] = $row->member_since;
            $currentTeamUsers[$key]->auto_tags = $row->auto_tags;

            $assigned_permissions = [];
            if ($row->user_permissions) {
                foreach ($row->user_permissions as $i_row) {
                    $assigned_permissions[] = $i_row->id;
                }
            }
            $currentTeamUsers[$key]->assigned_permissions = $assigned_permissions;

            unset($row->user_permissions);
            unset($currentTeamUsers[$key]->user_permissions);
        }

        return [
            'id' => $currentTeam->id,
            'name' => $currentTeam->name,
            'role' => $currentTeam->pivot->role,
            'owner_id' => $currentTeam->owner_id,
            'photo_url' => $currentTeam->photo_url,
            'current_billing_plan' => $currentTeam->current_billing_plan,
            'slug' => $currentTeam->slug,
            'users' => $currentTeamUsers,
            'trial_ends_at' => self::changeDateFormat($currentTeam->trial_ends_at),
            'created_at' => self::changeDateFormat($currentTeam->created_at),
            'updated_at' => self::changeDateFormat($currentTeam->updated_at),
        ];
    }

    private static function changeDateFormat($date)
    {
        return date('M d, Y', strtotime(time_according_to_user_time_zone($date))) . ' at ' . date('g:i A', strtotime(time_according_to_user_time_zone($date)));
    }

    function failed_payment(){
        return $this->hasOne('App\FailedPayment', 'user_id');
    }

    /**
     * User settings relation
     *
     * @return HasOne
     */
    public function settings()
    {
        return $this->hasOne('App\UserSettings');
    }

    public static function saveDashboardSetting($setting_index)
    {
        $user = request()->user();
        $settings = json_decode($user->settings->dashboard_settings);
        if (!in_array($setting_index, $settings)) {
            $settings[] = $setting_index;
            $user->settings->dashboard_settings = json_encode($settings);
            $user->settings->save();
        }
    }


    /**
     * Permissions
     * @return BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany('App\Permission', 'user_permissions', 'user_id', 'permission_id');
    }

    /**
     * Users permissions with permission
     * @return BelongsToMany
     */
    public function user_permissions()
    {
        return $this->belongsToMany('App\Permission', 'user_permissions', 'user_id', 'permission_id')
            ->wherePivot('permission', 1);
    }

    /**
     * User auto tags relation
     * @return HasMany
     */
    public function auto_tags()
    {
        return $this->hasMany('App\ContactAutoTag');
    }

    /**
     * User auto tags relation
     * @return HasMany
     */
    public function activity()
    {
        return $this->hasMany('App\ActivityLog');
    }

    /**
     * User Stage relation
     * @return HasOne
     */
    public function stages()
    {
        return $this->hasOne('App\Stage');
    }

    public function relate_user()
    {
        return $this->hasOne('App\User', 'relate_id', 'id');
    }

    public function relate_users()
    {
        return $this->hasMany('App\User', 'relate_id', 'id');
    }

    public function videos()
    {
        return $this->hasMany('App\Video', 'owner', 'id');
    }

    public function summary()
    {
        return $this->hasOne('App\AccountSummary');
    }

    /**
     * User full name
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        $fullName = trim("$this->first_name {$this->last_name}");

        return $fullName;
    }

    public function getFirstNameAttribute()
    {
        $nameAry = explode(' ', $this->name);
//        $name = trim($this->name);
//        $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
//        $first_name = trim(preg_replace('#' . $last_name . '#', '', $name));

        return isset($nameAry[0]) ? $nameAry[0] : $this->name;
    }

    public function getLastNameAttribute()
    {
//        $name = trim($this->name);
//        $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $nameAry = explode(' ', $this->name);

        return isset($nameAry[1]) ? $nameAry[1] : '';
    }


//    public function getCreatedAtAttribute($value) {
//        return $value;
//    }


//    public function getUpdatedAtAttribute($value) {
//        return $value;
//    }

//    public function getLastActivityAttribute($value) {
//        return $value;
//    }

//    /**
//     * Always save updated_at in user timezone format
//     */
//    public function setUpdatedAtAttribute()
//    {
//        $this->attributes['updated_at'] = now('UTC');
//    }

//    public function setLastActivityAttribute()
//    {
//        $this->attributes['last_activity'] = now('UTC');
//    }


    public function getMemberSinceAttribute()
    {
        $date = $this->created_at;

        return self::changeDateFormat($date);
    }

    /**
     * Last login attribute
     *
     * @return string
     */
    public function getLastLoginAttribute()
    {
//        $date = $this->last_activity;
        $date = $this->updated_at;

        return self::changeDateFormat($date);
    }

    /**
     * Mailer
     *
     * @return HasMany
     */
    public function integrations()
    {
        return $this->hasMany('App\Integration');
    }

    public function integration_lists()
    {
        return $this->hasMany('App\IntegrationList', 'user_id');
    }

    /**
     * Overwrite persist code
     *
     * http://stackoverflow.com/questions/17393833/laravel-enable-sentry-user-account-be-used-in-multiple-computers
     *
     * @return mixed|string
     */
    public function getPersistCode()
    {
        if (!$this->persist_code) {
            $this->persist_code = $this->getRandomString();

            // Our code got hashed
            $persistCode = $this->persist_code;

            $this->save();

            return $persistCode;
        }

        return $this->persist_code;
    }

    /**
     * Override the mail body for reset password notification mail.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordLinkSent($token));
    }

    public function currentPlan()
    {
        return $this->hasOne('App\Subscription');
    }

    public function getBillingStatus()
    {
        $subscription = $this->currentPlan;
        
        $plan = Spark::teamPlans()->where('id', $subscription->stripe_plan)->first();
        $trialEndsAt = new Carbon($subscription->trial_ends_at);
        $trialExpires = $trialEndsAt->diffInDays(Carbon::now());
        $endsAt = new Carbon($subscription->ends_at);
        $suspendAt = new Carbon($subscription->ends_at);
         
        $stripePlan = SignupCoupon::where(['plan_id'=>$plan->name,'status'=>'active'])->first();
        $discount = (isset($stripePlan->amount)?$stripePlan->amount:0);
        $plan->price = ($this->is_discount_applied=='1') ? $plan->price - $discount : $plan->price;
        $suspendAt->addDays(config('app.suspendPeriod'));

        return [
            'isActive' => $endsAt->diffInDays(Carbon::now()) > 0,
            'isSuspended' => $suspendAt->diffInDays(Carbon::now()) <= 0,
            'trialDays' => $plan->trialDays,
            'trialExpires' => $trialExpires > 0 ? $trialExpires : 0,
            'amount' => $plan->price,
            'interval' => $plan->interval,
            'billingDate' => $subscription->ends_at,
            'discount' => $discount,
            'is_discount_applied' => $this->is_discount_applied,
            'suspendDate' => $suspendAt->toDateTimeString(),
        ];
    }

    public function getBillingEstimate()
    {
        $monthly_bandwidth_usage = $this->summary->bandwidth_usage;
        $plan = Spark::teamPlans()->where('id', $this->currentPlan->stripe_plan)->first();

        $result = [
            'subscriptionCost' => $plan->price,
        ];

        if (isset($plan->attributes['overage_const_per_gb'])) {
            $result['bandwidthOverage'] = $this->getBandwidthOverage();
            $result['total'] = $plan->price + $result['bandwidthOverage']->cost;
        } else {
            $result['total'] = $plan->price;
        }

        return $result;
    }

    public static function getColumnByPermissionsUser($id)
    {
        $userPermission = Permission::where('user_id', Auth::user()->id)
            ->leftjoin('user_permissions', 'user_permissions.permission_id', 'permissions.id')
            ->get();
        $columnsName = Config::get('customizeColumns.columnsName');
        $columnsData = Config::get('customizeColumns.columnsData');

        $accessColumns = [$columnsName['user']];

        if ($id) {
            $accessColumns[] = $columnsName['role'];
        } else {
            $accessColumns[] = $columnsName['relatedUsers'];
        }

        $visibleFields = $userPermission->map(
            function ($permission)
            use (&$accessColumns, $columnsName, $columnsData) {

                if ($permission->name === 'View Analytics') {
                    $accessColumns[] = $columnsName['project'];
                    $accessColumns[] = $columnsName['plan'];
                    $accessColumns[] = $columnsName['location'];
                    $accessColumns[] = $columnsName['tags'];
                    $accessColumns[] = $columnsName['businessInfo'];
                    return [
                        $columnsData['project'],
                        $columnsData['plan'],
                        $columnsData['location'],
                        $columnsData['tags'],
                        $columnsData['businessInfo'],
                    ];
                }

                if ($permission->name === 'View Contacts') {
                    $accessColumns[] = $columnsName['subscriptionStatus'];
                    $accessColumns[] = $columnsName['lastActivity'];
                    $accessColumns[] = $columnsName['views'];
                    $accessColumns[] = $columnsName['bandwidth'];
                    $accessColumns[] = $columnsName['age'];
                    return [
                        $columnsData['relatedUsers'],
                        $columnsData['subscriptionStatus'],
                        $columnsData['lastActivity'],
                        $columnsData['views'],
                        $columnsData['bandwidth'],
                        $columnsData['age'],
                    ];
                }

                if ($permission->name === 'Project') {
                    $accessColumns[] = $columnsName['contactSize'];
                    $accessColumns[] = $columnsName['compliance'];
                    return [
                        $columnsData['contactSize'],
                        $columnsData['compliance'],
                    ];
                }
            })->collapse()->collapse();

        return [
            'visibleFields' => $visibleFields,
            'accessColumns' => $accessColumns,
        ];
    }

    public static function getLastRegUsers($form)
    {
        $limit = isset($form['limit'])
            ? $form['limit']
            : 10;
        $users = User::select([
            'users.id as id',
            'users.name as name',
            'users.email as email',
            'settings.city as city',
            'settings.state as state',
            'settings.country as country',
            'users.trial_ends_at as trialEnds',
            'subscriptions.stripe_plan as stripe_plan',
        ])
            ->leftJoin('subscriptions', 'subscriptions.user_id', '=', 'users.id')
            ->leftJoin('settings', 'settings.user_id', '=', 'users.id')
            ->orderBy('users.created_at', 'desc')
            ->limit($limit)
            ->get();

        $usersWithPlan = $users->map(function ($user) {
            $plan = Spark::teamPlans()->where('id', $user->stripe_plan)->first();
            $user->plan = $plan ? $plan->name : null;
            return $user;
        });

        return $usersWithPlan->makeHidden(['stripe_plan']);
    }

    public static function getListLocation($form)
    {
        return UserSettings::groupBy($form)->orderBy($form, 'asc')->pluck($form)->toArray();
    }

    public static function filterUsers($id, $filterData, $status)
    {
        $visibleFields = User::getColumnByPermissionsUser($id);
        $existTag = count(array_filter($visibleFields['accessColumns'], function ($column) {
            $columnTags = Config::get('customizeColumns.columnsName.tags');
            return $column === $columnTags;
        }));
        $statusId = DB::table('statuses')->where('name', $status)->first()->id;
        $activeStatusId = DB::table('statuses')->where('name', 'active')->first()->id;
        $users = User::select([
            'users.id as id',
            'users.name as name',
            'users.email as email',
            'team_users.role as role',
            'users.photo_url as avatar',
            'users.billing_status as status',
            'users.last_activity as lastActivity',
            'users.created_at as createdAt',
            'subscriptions.created_at as firstUpgrade',
            'subscriptions.updated_at as lastRenewed',
            'subscriptions.ends_at as renewalDue',
            'subscriptions.trial_ends_at as trialEnd',
            'settings.city as city',
            'settings.state as state',
            'settings.country as country',
            'settings.company as company',
            'settings.zip_code as zipCode',
            'settings.street_address as street',
            'subscriptions.stripe_plan as stripe_plan',
            'account_summary.contact_size as contactSize',
            'account_summary.videos_count as videosCount',
            'account_summary.videos_views as videoViews',
            'account_summary.projects_count as projectsCount',
            'account_summary.views_total_watch_time as totalWatchTime',
            'account_summary.bandwidth_usage as bandwidthCurrentCycle',
            'account_summary.bandwidth_all_time as allTimeBandwidth',
            'account_summary.compliance as compliance',
            DB::raw('(SELECT statuses.name FROM statuses WHERE statuses.id = users.status_id) as accountStatus'),
            DB::raw('(SELECT count(*) FROM team_users relatedUsers WHERE relatedUsers.team_id = users.current_team_id) as relatedAllUsers'),
            DB::raw("(SELECT count(*) FROM users relatedUsers 
            WHERE id IN (SELECT user_id FROM team_users WHERE team_id = users.current_team_id) 
            AND status_id = $activeStatusId) as relatedActiveUsers"),
            DB::raw('(SELECT relatedUsers.name FROM users relatedUsers WHERE relatedUsers.id = users.relate_id) as managerAccount'),
        ])
            ->leftJoin('account_summary', 'account_summary.user_id', '=', 'users.id')
            ->leftJoin('teams', 'teams.owner_id', '=', 'users.id')
            ->leftJoin('team_users', 'team_users.user_id', '=', 'users.id')
            ->leftJoin('subscriptions', 'subscriptions.user_id', '=', 'users.id')
            ->leftJoin('settings', 'settings.user_id', '=', 'users.id')
            ->leftJoin('subscribers', 'subscribers.user_id', 'users.id')
            ->when($id, function ($query, $id) use ($statusId) {
                $query
                    ->whereRaw("users.id IN (SELECT id FROM users WHERE id IN (SELECT user_id from team_users WHERE team_id = (SELECT id FROM teams Where owner_id = $id)))")
                    ->where('users.status_id', $statusId);
            })
            ->when($statusId, function ($query, $statusId) {
                $query->where('users.status_id', $statusId);
            })
            ->when(isset($filterData['tag']) || $existTag && $filterData['columnTag'], function ($query) use ($filterData, $existTag) {
                $tag = isset($filterData['tag']) ? $filterData['tag'] : null;
                $query->leftJoin('contact_auto_tags', 'contact_auto_tags.user_id', 'users.id');
                if ($tag) {
                    $query->where('contact_auto_tags.tag', 'LIKE', "%$tag%");
                }
                $query->when($existTag && $filterData['columnTag'], function ($subQuery) {
                    $subQuery->addSelect(DB::raw('json_arrayagg(contact_auto_tags.tag) as tags'));
                });
            })
            ->when(isset($filterData['search']), function ($query) use ($filterData) {
                $search = $filterData['search'];
                $query
                    ->where('users.name', 'LIKE', "%$search%")
                    ->orWhere('users.email', 'LIKE', "%$search%");
            })
            ->when(isset($filterData['statusPlan']), function ($query) use ($filterData) {
                $query->where('users.billing_status', '=', $filterData['statusPlan']);
            })
            ->when(isset($filterData['plan']), function ($query) use ($filterData) {
                $plan = $filterData['plan'];
                $query->where('subscriptions.stripe_plan', 'LIKE', "%$plan%");
            })
            ->when(isset($filterData['userType']), function ($query) use ($filterData) {
                $userType = $filterData['userType'];
                $query->where('team_users.role', '=', $userType);
            })
            ->when(isset($filterData['location']['country']), function ($query) use ($filterData) {
                $country = $filterData['location']['country'];
                $query->where('settings.country', 'LIKE', "%$country%");
            })
            ->when(isset($filterData['location']['city']), function ($query) use ($filterData) {
                $city = $filterData['location']['city'];
                $query->where('settings.city', 'LIKE', "%$city%");
            })
            ->when(isset($filterData['location']['state']), function ($query) use ($filterData) {
                $state = $filterData['location']['state'];
                $query->where('settings.state', 'LIKE', "%$state%");
            })
            ->when(isset($filterData['lastActivity']['between']['from']) || isset($filterData['lastActivity']['greater']), function ($query) use ($filterData) {
                $min = isset($filterData['lastActivity']['between']['from']) ? $filterData['lastActivity']['between']['from'] : $filterData['lastActivity']['greater'];
                $query->where('users.last_activity', '>', $min);
            })
            ->when(isset($filterData['lastActivity']['between']['to']) || isset($filterData['lastActivity']['less']), function ($query) use ($filterData) {
                $max = isset($filterData['lastActivity']['between']['to']) ? $filterData['lastActivity']['between']['to'] : $filterData['lastActivity']['less'];
                $query->where('users.last_activity', '<', $max);
            })
            ->when(isset($filterData['lastActivity']['equal']), function ($query) use ($filterData) {
                $query->where('users.last_activity', '=', $filterData['lastActivity']['equal']);
            })
            ->when(isset($filterData['createdAt']['between']['from']) || isset($filterData['createdAt']['greater']), function ($query) use ($filterData) {
                $min = isset($filterData['createdAt']['between']['from']) ? $filterData['createdAt']['between']['from'] : $filterData['createdAt']['greater'];
                $query->where('users.created_at', '>', $min);
            })
            ->when(isset($filterData['createdAt']['between']['to']) || isset($filterData['createdAt']['less']), function ($query) use ($filterData) {
                $max = isset($filterData['createdAt']['between']['to']) ? $filterData['createdAt']['between']['to'] : $filterData['createdAt']['less'];
                $query->where('users.created_at', '<', $max);
            })
            ->when(isset($filterData['createdAt']['equal']), function ($query) use ($filterData) {
                $date = date_create($filterData['createdAt']['equal']);
                date_modify($date, '+1 day');
                $query
                    ->where('users.created_at', '>', $filterData['createdAt']['equal'])
                    ->where('users.created_at', '<', $date);
            })
            ->when(isset($filterData['contactSize']['between']['from']) || isset($filterData['contactSize']['greater']), function ($query) use ($filterData) {
                $min = isset($filterData['contactSize']['between']['from']) ? $filterData['contactSize']['between']['from'] : $filterData['contactSize']['greater'];
                $query->where('account_summary.contact_size', '>', $min);
            })
            ->when(isset($filterData['contactSize']['between']['to']) || isset($filterData['contactSize']['less']), function ($query) use ($filterData) {
                $max = isset($filterData['contactSize']['between']['to']) ? $filterData['contactSize']['between']['to'] : $filterData['contactSize']['less'];
                $query->where('account_summary.contact_size', '<', $max);
            })
            ->when(isset($filterData['contactSize']['equal']), function ($query) use ($filterData) {
                $query->where('account_summary.contact_size', '=', $filterData['contactSize']['equal']);
            })
            ->when(isset($filterData['relatedUsers']['between']['from']) || isset($filterData['relatedUsers']['greater']), function ($query) use ($filterData) {
                $min = isset($filterData['relatedUsers']['between']['from']) ? $filterData['relatedUsers']['between']['from'] : $filterData['relatedUsers']['greater'];
                $query->where(DB::raw('(SELECT count(*) FROM team_users relatedUsers WHERE relatedUsers.team_id = teams.id)'),
                    '>', $min);
            })
            ->when(isset($filterData['relatedUsers']['between']['to']) || isset($filterData['relatedUsers']['less']), function ($query) use ($filterData) {
                $max = isset($filterData['relatedUsers']['between']['to']) ? $filterData['relatedUsers']['between']['to'] : $filterData['relatedUsers']['less'];
                $query->where(DB::raw('(SELECT count(*) FROM team_users relatedUsers WHERE relatedUsers.team_id = teams.id)'),
                    '<', $max);
            })
            ->when(isset($filterData['relatedUsers']['equal']), function ($query) use ($filterData) {
                $query->where(DB::raw('(SELECT count(*) FROM team_users relatedUsers WHERE relatedUsers.team_id = teams.id)'),
                    '=', $filterData['relatedUsers']['equal']);
            })
            ->when(isset($filterData['views']['between']['from']) || isset($filterData['views']['greater']), function ($query) use ($filterData) {
                $min = isset($filterData['views']['between']['from']) ? $filterData['views']['between']['from'] : $filterData['views']['greater'];
                $query->where('account_summary.videos_views', '>', $min);
            })
            ->when(isset($filterData['views']['between']['to']) || isset($filterData['views']['less']), function ($query) use ($filterData) {
                $max = isset($filterData['views']['between']['to']) ? $filterData['views']['between']['to'] : $filterData['views']['less'];
                $query->where('account_summary.videos_views', '<', $max);
            })
            ->when(isset($filterData['views']['equal']), function ($query) use ($filterData) {
                $query->where('account_summary.videos_views', '=', $filterData['views']['equal']);
            })
            ->when(isset($filterData['bandwidthUsage']['between']['from']) || isset($filterData['bandwidthUsage']['greater']), function ($query) use ($filterData) {
                $min = isset($filterData['bandwidthUsage']['between']['from']) ? $filterData['bandwidthUsage']['between']['from'] : $filterData['bandwidthUsage']['greater'];
                $query->whereRaw("round(account_summary.bandwidth_usage / 1024 / 1024, 2) > $min");
            })
            ->when(isset($filterData['bandwidthUsage']['between']['to']) || isset($filterData['bandwidthUsage']['less']), function ($query) use ($filterData) {
                $max = isset($filterData['bandwidthUsage']['between']['to']) ? $filterData['bandwidthUsage']['between']['to'] : $filterData['bandwidthUsage']['less'];
                $query->whereRaw("round(account_summary.bandwidth_usage / 1024 / 1024, 2) < $max");
            })
            ->when(isset($filterData['bandwidthUsage']['equal']), function ($query) use ($filterData) {
                $equal = $filterData['bandwidthUsage']['equal'];
                $query->whereRaw("round(account_summary.bandwidth_usage / 1024 / 1024, 2) = $equal");
            })
            ->when(isset($filterData['byDate']), function ($query) use ($filterData) {
                $query->orderBy('users.created_at', $filterData['byDate']);
            })
            ->when(isset($filterData['byName']), function ($query) use ($filterData) {
                $sortParam = $filterData['byName'] === 'asc' ? 'desc' : 'asc';
                $query->orderBy('name', $sortParam);
            })
            ->when(isset($filterData['byNoRelatedUsers']), function ($query) use ($filterData) {
                $query->orderBy('relatedAllUsers', $filterData['byNoRelatedUsers']);
            })
            ->when(isset($filterData['byContactSize']), function ($query) use ($filterData) {
                $query->orderBy('contactSize', $filterData['byContactSize']);
            })
            ->when(isset($filterData['byViewCount']), function ($query) use ($filterData) {
                $query->orderBy('videoViews', $filterData['byViewCount']);
            })
            ->when(isset($filterData['byBandwidthUsage']), function ($query) use ($filterData) {
                $query->orderBy('allTimeBandwidth', $filterData['byBandwidthUsage']);
            })
            ->when(isset($filterData['byNoOfVideos']), function ($query) use ($filterData) {
                $query->orderBy('videosCount', $filterData['byNoOfVideos']);
            })
            ->when($id, function ($query, $id) {
                $query
                    ->orWhere('users.id', $id);
            })
            ->groupBy('users.id');
        $totalUsers = count($users->get());

        $findUsers = $users
            ->when($filterData['pagination']['offset'], function ($query, $offset) {
                $query->offset($offset);
            })
            ->when($filterData['pagination']['limit'], function ($query, $limit) {
                $query->limit($limit);
            })
            ->get();

        $findPlanForUsers = $findUsers->map(function ($user) {
            $trial_days = config('services.subscription.trial_duration');
            $renewal = $user->trialEnd > date('m-d-y', strtotime("+$trial_days days"))
                ? $user->trialEnd
                : $user->renewalDue;
            $user->renewalDue = $renewal;
            $plan = Spark::teamPlans()->where('id', $user->stripe_plan)->first();
            $user->plan = $plan ? $plan->name : null;
            $user->cycle = $plan ? $plan->interval : null;
            $user->bandwidthLimit = $plan ? $plan->attributes['bandwidth_limit'] : null;
            return $user;
        });
        return [
            'accessColumns' => $visibleFields['accessColumns'],
            'totalUsers' => $totalUsers,
            'users' => $findPlanForUsers
                ->makeHidden([
                    'status',
                    'firstUpgrade',
                    'lastRenewed',
                    'plan',
                    'videoViews',
                    'city',
                    'state',
                    'country',
                    'tag',
                    'allTimeBandwidth',
                    'relatedAllUsers',
                    'relatedActiveUsers',
                    'contactSize',
                    'managerAccount',
                    'videosCount',
                    'projectsCount',
                    'stripe_plan',
                    'cycle',
                    'renewalDue',
                    'lastActivity',
                    'zipCode',
                    'street',
                    'company',
                    'totalWatchTime',
                    'bandwidthCurrentCycle',
                    'compliance',
                    'createdAt',
                    'bandwidthLimit',
                ])
                ->makeVisible($visibleFields['visibleFields']->toArray()),
        ];
    }

    public static function getUserComplianceById($id)
    {
        return ComplianceRecords::where('user_id', $id)
            ->select([
                'compliance_records.id as id',
                'compliance_records.resolution as resolution',
                'compliance_records.created_at as createdAt',
                'compliance_records.ends_at as endsAt',
                'issues.title as title',
                'issues.description as description',
                'users.name as moderatorName',
                'users.id as moderatorId',
            ])
            ->leftJoin('issues', 'issues.id', '=', 'compliance_records.issue_id')
            ->leftJoin('users', 'users.id', '=', 'compliance_records.moderator_user_id')
            ->get();
    }

    public static function getUserInfoById($id)
    {
        $user = User::select([
            'users.id as id',
            'users.photo_url as avatar',
            'users.name as name',
            'users.email as email',
            'users.payment_method as payment_method',
            'users.exp_month as card_exp_month',
            'users.exp_year as card_exp_year',
            'users.paypal_email as paypal_email',
            'team_users.role as role',
            'settings.company as company',
            'settings.city as city',
            'settings.country as country',
            'settings.timezone as timezone',
            'settings.stage_public_url as website',
            'users.last_activity as lastActivity',
            'users.created_at as createdAt',
            'users.billing_status as status',
            'users.card_last_four as card_last_four',
            'users.card_brand as cardBrand',
            'subscriptions.name as plan',
            'subscriptions.created_at as firstUpgrade',
            'subscriptions.updated_at as lastRenewed',
            'subscriptions.ends_at as renewalDue',
            DB::raw('(SELECT relatedUsers.name FROM users relatedUsers WHERE relatedUsers.id = users.relate_id) as managerAccount'),

        ])
            ->where('users.id', $id)
            ->leftJoin('settings', 'settings.user_id', 'users.id')
            ->leftJoin('subscriptions', 'subscriptions.user_id', 'users.id')
            ->leftJoin('team_users', 'team_users.user_id', '=', 'users.id')
            ->with('settings')
            ->first();

        $cycle = Spark::teamPlans()
            ->where('id', $user->currentPlan()->first()->stripe_plan)
            ->first()
            ->interval;

        $user->cycle = $cycle;
        return ['user' => $user];
    }

    public static function addTagForUser($id, $data)
    {
        $tag = $data['tag'];
        $user = User::leftJoin('contact_auto_tags', 'contact_auto_tags.user_id', 'users.id')
            ->where('users.id', $id)
            ->where('contact_auto_tags.tag', $tag)
            ->first();

        if ($user) {
            return app()->abort(403, 'This is tag already');
        }

        return $tag;
    }

    public function cancelSubscriptionPayPal()
    {
        $gateway = new Braintree_Gateway(config('services.braintree'));
        $subscription = $this->currentPlan()->orderBy('id', 'desc')->first();
        $status = $gateway->subscription()->cancel($subscription->stripe_id);

        return (object)[
            'subscription' => $subscription,
        ];
    }

    public function cancelSubscriptionStripe()
    {
        $subscription = $this->currentPlan()->orderBy('id', 'desc')->first();
        $status = $this->subscription($subscription->name)->cancelNow();

        return (object)[
            'subscription' => $subscription,
            'status' => $status,
        ];
    }

    public function createSubscriptionBraintree($current_sub, $pm)
    {
        $gateway = new Braintree_Gateway(config('services.braintree'));

        $customer = $gateway->customer()->find($this->braintree_id);

        $paymentMethod = $gateway->paymentMethod()->create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $pm,
        ]);

        $new_sub = $gateway->subscription()->create([
            'paymentMethodToken' => $paymentMethod->paymentMethod->token,
            'planId' => $current_sub->stripe_plan,
            'trialPeriod' => 'false'
        ]);

        $this->trial_ends_at = null;
        $this->save();

        $subscription = \App\Subscription::create();
        $subscription->stripe_plan = $current_sub->stripe_plan;
        $subscription->name = $current_sub->name;
        $subscription->stripe_id = $new_sub->subscription->id;
        $subscription->user_id = $this->id;
        $subscription->trial_ends_at = null;
        $subscription->ends_at = now()->addMonths(1);
        $subscription->save();

        return $subscription;
    }

    public function changePaymentMethod($data, $token)
    {
        switch (strtolower($data['payment_method'])) {
            case 'stripe':
                $result = $this->cancelSubscriptionPayPal();
                Subscription::where('user_id', $this->id)->delete();
                $this
                    ->newSubscription($result->subscription->name, $result->subscription->stripe_plan)
                    ->skipTrial()
                    ->create($token);
                $this->payment_method = $data['payment_method'];
                $this->save();
                return $this;
            case 'paypal':
                $result = $this->cancelSubscriptionStripe();
                $this->payment_method = $data['payment_method'];
                Subscription::where('user_id', $this->id)->delete();
                $this->save();
                $new_sub = $this
                    ->createSubscriptionBraintree($result->subscription, $token);
                return $new_sub;

            default:
                return false;
        }
    }

    public function getBandwidthOverage()
    {
        $bandwidth_usage = $this->summary->bandwidth_usage;
        $plan = Spark::teamPlans()->where('id', $this->currentPlan->stripe_plan)->first();
        $overage = ($bandwidth_usage / 1024 / 1024 / 1024) - $plan->attributes['bandwidth_limit'];

        return (object)[
            'size' => $overage > 0 ? $overage : 0,
            'cost' => $overage > 0 ? $overage * $plan->attributes['bandwidth_per_item'] : 0,
            'charge'=> $plan->attributes['bandwidth_per_item']
        ];
    }

    public function includeOverageInPricePaypal($cost)
    {
        $gateway = new Braintree_Gateway(config('services.braintree'));
        $subscription = $gateway->subscription()->find($this->currentPlan()->first()->stripe_id);
        $addOnOverageBandwidth = Config::get('addons.overageBandwidth');
        $hasAddOn = array_filter($subscription->addOns, function ($addOn) use ($addOnOverageBandwidth) {
            return $addOn->id === $addOnOverageBandwidth;
        });

        if (count($hasAddOn)) {
            return $gateway->subscription()->update($this->currentPlan()->first()->stripe_id, [
                'addOns' => [
                    'add' => [],
                    'update' => [
                        [
                            'existingId' => $addOnOverageBandwidth,
                            'amount' => $cost
                        ]
                    ],
                ],
                'discounts' => []
            ]);
        }
        if (!count($hasAddOn)) {
            return $gateway->subscription()->update($this->currentPlan()->first()->stripe_id, [
                'addOns' => [
                    'add' => [
                        [
                            'inheritedFromId' => $addOnOverageBandwidth,
                            'amount' => $cost
                        ]
                    ],
                    'update' => [],
                ],
                'discounts' => []
            ]);
        }
    }

    public function includeOverageInPriceStripe($cost)
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        $exist_items = collect(StripeInvoiceItem::all(['customer' => $this->stripe_id])->data);
        $overage_item = $exist_items->where('description', 'Adilo Overage Charge')->first();

        $options = [
            'customer' => $this->stripe_id,
            'amount' => $cost * 100,
            'currency' => 'usd',
            'description' => 'Adilo Overage Charge',
        ];

        if ($overage_item) {
            return StripeInvoiceItem::update($overage_item->id, [
                'amount' => $options['amount']
            ]);
        }

        return StripeInvoiceItem::create($options);
    }

    public function includeOverage($cost)
    {
        switch ($this->payment_method) {
            case 'stripe':
                return $this->includeOverageInPriceStripe($cost);
            case 'paypal':
                return $this->includeOverageInPricePaypal($cost);
            default:
                return false;
        }

    }

    public static function searchPlanStatus($searchData, $status)
    {
        $statusId = DB::table('statuses')->where('name', $status)->first()->id;
        $filteredStatus = User::select([
            'users.billing_status as planStatus',
            'users.id as id',
            DB::raw("(SELECT count(*) FROM users ub WHERE ub.billing_status = users.billing_status AND ub.status_id = $statusId) 
                as usersCount"),
        ])
            ->when(isset($searchData['offset']), function ($query) use ($searchData) {
                $query->offset($searchData['offset']);
            })
            ->when(isset($searchData['limit']), function ($query) use ($searchData) {
                $query->limit($searchData['limit']);
            })
            ->when($statusId, function ($query, $statusId) {
                $query->where('users.status_id', '=', $statusId);
            })
            ->groupBy('users.billing_status')
            ->orderBy('usersCount', 'desc')
            ->get();

        return [
            'allStatusCount' => count($filteredStatus),
            'filteredStatus' => $filteredStatus,
        ];
    }

    public function recalcPlanChange($dest_plan_id, $change_date)
    {
        $subscription = $this->currentPlan;
        $current_plan = Spark::teamPlans()
            ->where('id', $subscription->stripe_plan)
            ->first();
        $dest_plan = Spark::teamPlans()
            ->where('id', $dest_plan_id)
            ->first();
        $ends_at = new Carbon($subscription->ends_at);
        $start_at = new Carbon($change_date);

        $total_days = Carbon::now()->daysInMonth;
        $credit_days = $start_at->diffInDays($ends_at);
        $credit = round(($current_plan->price / $total_days) * $credit_days, 2);

        return $dest_plan->price - $credit;
    }

    public function changePlan($data, $payment)
    {
        $current_sub = $this->currentPlan;

        if ($this->payment_method === 'stripe') {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $stripe_sub = \Stripe\Subscription::retrieve($current_sub->stripe_id);
            $billing_cycle_anchor = $data['cycle_option'] === 'today' ? 'now' : 'unchanged';
            $current_sub->ends_at = now()->addMonths(1);

            if ($data['cycle_option'] === 'on_date') {
                $date = new Carbon($data['date']);
                $billing_cycle_anchor = $date->timestamp;
            }

            return \Stripe\Subscription::update($current_sub->stripe_id, [
                'cancel_at_period_end' => $data['cycle_option'] === 'end_cycle',
                'items' => [
                    [
                        'id' => $stripe_sub->items->data[0]->id,
                        'plan' => $data['newPlan'],
                        'billing_cycle_anchor' => $billing_cycle_anchor,
                    ]
                ]
            ]);
        }

        if ($this->payment_method === 'paypal' && $data['cycle_option'] === 'today') {
            $gateway = new Braintree_Gateway(config('services.braintree'));
            $token = $gateway->customer()->find($this->braintree_id)->paymentMethods[0]->token;
            $this->cancelSubscriptionPayPal();
            $price = $payment
                ? Spark::allPlans()->where('id', $data['newPlan'])->first()->price
                : $payment;

            $planInterval = Spark::allPlans()->where('id', $data['newPlan'])->first()->interval;
            $new_sub = $gateway->subscription()->create([
                'paymentMethodToken' => $token,
                'planId' => $data['newPlan'],
                'price' => $price,
                'trialPeriod' => false
            ]);

            $interval = $planInterval === 'monthly' ? '+1 month' : '+1 year';

            $current_sub->stripe_id = $new_sub->subscription->id;
            $current_sub->stripe_plan = $data['newPlan'];
            $current_sub->trial_ends_at = null;
            $current_sub->ends_at = date('Y-m-d H:i:s', strtotime($interval));
            $current_sub->save();

            $this->billing_status = 'Active';
            $this->trial_ends_at = null;
            $this->save();

            return $new_sub;
        }
    }

    public function addNote($userId, $text)
    {
        $note = Note::create();
        $note->user_id = $userId;
        $note->text = $text;
        $note->author_id = $this->id;
        $note->save();

        $note->author = $this->name;
        return $note;
    }

    public function notes()
    {
        return Note::select([
            'notes.id',
            'notes.text',
            'users.name as author',
            'notes.created_at',
            'notes.updated_at',
        ])
            ->leftJoin('users', 'users.id', '=', 'notes.author_id')
            ->where('user_id', $this->id)
            ->get();
    }

    public function editNote($noteId, $text)
    {
        $note = Note::where('id', $noteId)
            ->where('user_id', $this->id)
            ->first();

        if (!$note) {
            return abort(404, 'Note not found');
        }

        $note->text = $text;
        $note->save();

        return $note;
    }

    public function deleteNote($noteId)
    {
        $note = Note::where('id', $noteId)
            ->where('user_id', $this->id)
            ->first();

        if (!$note) {
            return abort(404, 'Note not found');
        }

        $note->delete();
    }

    public function unsuspend($complianceId)
    {
        $date_now = date("Y-m-d H:i:s");
        $compliance = ComplianceRecords::where('id', $complianceId)
            ->where('user_id', $this->id)
            ->where('ends_at', '>', $date_now)
            ->first();

        if (!$compliance) {
            return abort(404, 'Compliance not found');
        }

        $activeStatusId = Status::where('name', 'active')->first()->id;
        $compliance->ends_at = $date_now;
        $compliance->save();

        $this->status_id = $activeStatusId;
        $this->save();
        Mail::send('/billing/unsuspend', [
            'full_name' => $this->full_name,
//                'due_date' => isset($compliance->ends_at) ? 'Until ' . $compliance->ends_at : 'Forever',

        ], function ($m) {
            $m->to($this->email)->from('compliance@bigcommand.com', 'Bigcommand Compliance')->subject('Your Adilo account has been restored!');
        });

        return 'User is unsuspended';
    }

    public function suspend($validatedData, $moderator, $blockMail = false)
    {
        $this->suspended()->delete();

        $compliance = ComplianceRecords::create([
            'user_id' => $this->id,
            'issue_id' => $validatedData['issue'],
            'resolution' => $validatedData['resolution'],
            'moderator_user_id' => $moderator->id,
            'email_text' => isset($validatedData['email_text']) ? $validatedData['email_text'] : null,
        ]);

        if ($validatedData['resolution'] === 'suspended') {
            if ($validatedData['days'] == 0) {
                $suspendTime = null;
            } else {
                $suspendTime = date('Y-m-d H:i:s', strtotime("+$validatedData[days] days"));
            }

            $suspended_status_id = Status::where('name', 'suspended')->first()->id;
            $compliance->ends_at = $suspendTime;
            $this->status_id = $suspended_status_id;
            $this->billing_status = "suspended";

        }

        if ($validatedData['resolution'] === 'deleted') {
            $suspendTime = date('Y-m-d H:i:s', strtotime( $validatedData['days']));
            $suspended_status_id = Status::where('name', 'deleted')->first()->id;
            $compliance->ends_at = $validatedData['block_email'] ? null : $suspendTime;
            $this->status_id = $suspended_status_id;
            $this->billing_status = "Inactive";
            if ($blockMail) {
                $alreadyBlocked = BlockedEmail::where('email', $this->email)->first();
                if (!$alreadyBlocked) {
                    BlockedEmail::create([
                        'email' => $this->email
                    ]);
                }
            }
            $record = $this->suspended;
            $issueEntry = isset($record->issue_id) ? $record->issue : null;
            $issue = $issueEntry ? $issueEntry->title : 'Not Specified';
            Mail::send('/billing/deleted', [
                'full_name' => $this->full_name,
                'reason' => $issue
//                'reason' => $validatedData['description'],

            ], function ($m) {
                $m->to($this->email)->from('compliance@bigcommand.com', 'Bigcommand Compliance')->subject('Your Adilo account has been closed!');
            });
//            $this->notify(new AccountDeleted());
        }

        if ($validatedData['sub_users']) {
            $team = $this->currentTeam();
            $team_users = DB::table('team_users')
                ->where('team_id', $team->id)
                ->where('role', '=', 'subuser')
                ->get();
            $team_users->map(function ($team_user) use ($validatedData, $moderator) {
                $user = User::where('id', $team_user->user_id)->first();
                $validatedData['sub_users'] = false;
                $user->suspend($validatedData, $moderator);
            });
        }

        $this->save();
        $compliance->save();


        if ($validatedData['notify']) {
            $reason = Issue::query()->find($validatedData['issue']);
//            return isset($compliance->ends_at) ? 'Until ' . $compliance->ends_at : 'Forever';
            $text = isset($validatedData['email_text']) ? $validatedData['email_text'] : '';
            Mail::send('/billing/estimate', [
                'full_name' => $this->full_name,
                'email' => $this->email,
                'due_date' => isset($compliance->ends_at) ? 'Until ' . $compliance->ends_at : 'forever',
                'reason' => $reason->description,

            ], function ($m) {
                $m->to($this->email)->from('compliance@bigcommand.com', 'Bigcommand Compliance')->subject('Your Adilo Account Has Been Suspended');
            });
        }

        return "User is $validatedData[resolution]";
    }

    public function addBonusBandwidth($bonus)
    {
        $setting = $this->settings;
        $setting->bonus_bandwidth = $bonus;
        $setting->save();

        return "Add $bonus GB bandwidth usage by limit";
    }

    public function editProfile($validateData)
    {
        $pieces = explode(" ", $this->name);

        if (isset($validateData['email'])) {
            $new_email = $validateData['email'];
            $old = $this->email;
            $this->email = $validateData['email'];
//            Mail::send('/auth/new_pass', [
//                'full_name' => $this->full_name,
//                'old' => $old,
//                'name' => 'email',
//                'new' => $validateData['email'],
//            ], function ($m) {
//                $m->to($this->email)->subject('[Critical]Your Adilo profile has been updated')->from('support@bigcommand.com','BigCommand Support');;
//            });
            Mail::send('/billing/updated', [
                'full_name' => $this->full_name,
                'old' => $old,
                'name' => 'email',
                'new' => $validateData['email'],
            ], function ($m) {
                $m->to($this->email)->subject('[Critical]Your Adilo profile has been updated')->from('support@bigcommand.com','BigCommand Support');;
            });


        }

        if (isset($validateData['first_name'])) {
            $name = $validateData['first_name'] . ' ' . $pieces[1];
            $this->name = $name;


            Mail::send('/billing/updated', [
                'full_name' => $this->full_name,
                'old' => $this->first_name,
                'name' => 'first name',
                'new' => $validateData['first_name'],
            ], function ($m) {
                $m->to($this->email)->subject('[Critical]Your Adilo profile has been updated')->from('support@bigcommand.com','BigCommand Support');;
            });
        }

        if (isset($validateData['last_name'])) {
            $name = $pieces[0] . ' ' . $validateData['last_name'];
            $this->name = $name;
            Mail::send('/billing/updated', [
                'full_name' => $this->last_name,
                'old' => $this->last_name,
                'name' => 'last name',
                'new' => $validateData['last_name'],
            ], function ($m) {
                $m->to($this->email)->subject('[Critical]Your Adilo profile has been updated')->from('support@bigcommand.com','BigCommand Support');
            });
        }

        $this->save();

        if ($validateData['change_password']) {
        //return $validateData['password'];
            $permitted_chars = config('app.permitted_chars');
            $password = isset($validateData['password'])
                ? $validateData['password']
                : substr(str_shuffle($permitted_chars), 0, 10);

            $this->password = Hash::make($password);
            $this->save();

            $old = null;

            Mail::send('/billing/updated', [
                'full_name' => $this->full_name,
                'old' => $old,
                'name' => 'password',
                'new' => $password,
            ], function ($m) {
                $m->to($this->email)->subject('[Critical]Your Adilo profile has been updated')->from('support@bigcommand.com','BigCommand Support');;
            });

        }


//        if($validateData['notify'] && $validateData['change_password']) {
//               return      $validateData;
//            Mail::send('/auth/new_pass', [
//                'text' => $validateData['email_text'],
//                'password' => $password,
//            ],function ($m) {$m->to($this->email)->subject('New password');});
//        }
//
//        if($validateData['notify'] && !$validateData['change_password']) {
//            Mail::send('/auth/new_pass', [
//                'text' => $validateData['email_text'],
//            ], function ($m) {
//                $m->to($this->email)->subject('New password');
//            });
//        }
//
    }

    public function getUserSpaceUsage($owner)
    {
        return Video::select(DB::raw('SUM(total_size) as total_size'))->where('owner', $owner)->first()->total_size;


    }

    function getCycleDate($user)
    {
       
        
 
        $date = date('Y-m-d');
        $planINfo = Subscription::where('user_id', $user->id)->first();

        if ($planINfo != null) {
            
            $plan = Spark::teamPlans()->where('id', $user->currentPlan->stripe_plan)->first(); 
            $trial_ends_at = date('Y-m-d H:i:s', strtotime($planINfo->trial_ends_at));
            if ($trial_ends_at >= $date) {
                $cycledate = $trial_ends_at;
            } else {
                $day = date('Y-m-d', strtotime('+1 days ' . $trial_ends_at));
                if ($day >= date('Y-m-d')) {
                    $cycledate = $day;
                } else {
                    // $cycledate = date("Y-m-$day");
                    if($plan->interval=='monthly'){
                    $cycledate = $planINfo->ends_at;
                    $cycledate = date('Y-m-d H:i:s', strtotime('-1 months ' . $cycledate));
                    }else if($plan->interval=='yearly'){
                    $cycledate = $planINfo->ends_at;
                    $cycledate = date('Y-m-d H:i:s', strtotime('-1 year ' . $cycledate));
                    }
                    
                }
            }
        } else {
            return $date;
        }
        return $cycledate;
    }

    function getUserUsage($user)
    {
        $cycledate = $user->getCycleDate($user);
        $qry = Video::select(DB::raw('SUM(drm_sessions_count) as anti_piracy,SUM(forensic_sessions_count) as forensic_sessions_count,SUM(visual_sessions_count) as visual_sessions_count,SUM(caption_minutes) as caption_minutes,SUM(translation_minutes) as translation_minutes'))->where('owner', $user->id)->whereRaw(DB::raw("created_at>='$cycledate'"))->first();

        // $caption_translation =  Video::select(DB::raw(''))->where('owner', $user->id)->first();
        return $qry;

    }

    function getEnrichement($user)
    {
        $cycledate = $this->getCycleDate($user);
        return Subscriber::where('user_id', $user->id)
            ->whereRaw(DB::raw("created_at>='$cycledate'"))
            ->where(function ($q) {
                $q->where('organization', '!=', '')
                    ->orWhereNotNull('organization')
                    ->where('facebook_link', '!=', '')
                    ->orWhereNotNull('facebook_link')
                    ->where('photo_url', '!=', '')
                    ->orWhereNotNull('photo_url')
                    ->where('facebook_name', '!=', '')
                    ->orWhereNotNull('facebook_name')
                    ->where('linked_in_link', '!=', '')
                    ->orWhereNotNull('linked_in_link')
                    ->where('linked_in_name', '!=', '')
                    ->orWhereNotNull('linked_in_name')
                    ->where('twitter_link', '!=', '')
                    ->orWhereNotNull('twitter_link')
                    ->where('twitter_name', '!=', '')
                    ->orWhereNotNull('twitter_name')
                    ->where('job_title', '!=', '')
                    ->orWhereNotNull('job_title')
                    ->where('website', '!=', '')
                    ->orWhereNotNull('website')
                    ->where('interests', '!=', '')
                    ->orWhereNotNull('interests')
                    ->where('location', '!=', '')
                    ->orWhereNotNull('location')
                    ->where('gender', '!=', '')
                    ->orWhereNotNull('gender')
                    ->where('details', '!=', '')
                    ->orWhereNotNull('details');
            })->count();

    }


    public  function suspended()
    {
        return $this->hasOne(ComplianceRecords::class, 'user_id', 'id');

    }
    function calculatenedatforfreeplan($updated_at,$ens_at){
        $date = date('Y-m-d');
        $updated_at = date('Y-m-d',strtotime($updated_at));
        if($updated_at>$date){
            return $updated_at;
        }else{
            while(strtotime($updated_at)<strtotime($date)){
                $updated_at = date('Y-m-d',strtotime('+1 months '.$date));
            }
        }
        if($updated_at==$date){
            $updated_at = $ens_at;
        }
        return $updated_at;
    }
    function playlist(){
        return $this->hasMany('App\Playlist', 'owner', 'id');
    }
    function userCards(array $options = []){
        return $this->hasMany('App\Card', 'user_id', 'id');
    }

    public static function ownerPlan($user)
	{
		$spark = false;
		if ($user) {
			$userData = User::find($user->id);
			if ($userData) {
				$sub = $userData->currentPlan;
				if ($sub) {
					$spark = Spark::teamPlans()->where('id', $sub->stripe_plan)->first();
				}
			}
		}
		return $spark;
    }
    
    public function statistics()
    {
        return $this->hasMany('App\Statistic');
    }

    public function watchSessions()
    {
        return $this->hasMany('App\VideoWatchSession');
    }

    public function roomLabels(){
        return $this->hasMany('App\RoomLabel'); 
    }

    public function lifeTimePlan() {
        return $this->hasOne('App\PayKickSubscription');
    }

    public function bandwidthCycle()
    {
        return $this->hasOne('App\AccountSummary');
    }

}


