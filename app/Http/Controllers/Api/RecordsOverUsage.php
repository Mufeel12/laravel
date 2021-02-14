<?php

namespace App\Http\Controllers\Api;
use App\User;
use App\OverUsage;
use App\AccountSummary;
use App\SubscriptionItem;
use App\Http\Helpers\StripeHelper;
use App\Http\Controllers\Controller;
use Laravel\Spark\Spark;
use Carbon\Carbon;

class RecordsOverUsage extends Controller
{
function recordOverUsage(){
    //echo "<pre>";
    $users = User::join('subscriptions', function($join) {
        $join->on('users.id', '=', 'subscriptions.user_id');
      })
      ->where('subscriptions.stripe_plan','!=','free')
      ->where('users.id',101) 
      ->get();
      info('overage->>');
    if(count($users)>0){
         
        foreach($users as $user){
            $this->manageBandwidth($user);
            
        }
    }
}
function manageBandwidth($user){
    try{
        info('overage->>--'.json_encode($user));
    // info(json_encode($user));die;
    $user = User::where('id', $user->user_id)->first();
    if(isset($user->currentPlan->stripe_plan) && $user->currentPlan->stripe_plan!=''){
    $plan = Spark::allPlans()->where('id', $user->currentPlan->stripe_plan)->first();
    
    if($plan && $plan->name!='Free Forever'){
    $plantype = strtolower($plan->name);
    if($plantype=='business'){
        $plantype = $plan->interval === 'monthly'?"$plantype-monthly-metered":"$plantype-year-metered";
    }else{
        $plantype = $plan->interval === 'monthly'?"$plantype-monthly-metered":"$plantype-yearly-metered";
    }
    echo "<pre>";
    //print_r($plan);
    //$max_bandwidth_limit = $plan->attributes['bandwidth_limit']*1024*1024;die;
    $max_bandwidth_limit = 50*1024;
    $max_videos_limit = $plan->attributes['videos_limit'];
    $max_contacts_limit = $plan->attributes['contacts_limit'];
 
    $max_contacts_enrichment_limit = $plan->attributes['contacts_enrichment_limit'];
     
    // $max_video_captions_limit = $plan->attributes['video_captions_limit'];
    $max_video_captions_limit = 10;
    $max_translation_captions_limit = $plan->attributes['translation_captions_limit'];
    $userDetails = User::getUserDetails($user);
    // info(json_encode($userDetails)); 
    
    //bandwidth
     $used_bandwidth_cycle = $userDetails->bandwidth_cycle;
    // print_r($userDetails);
    
    $used_bandwidth_cycle = $used_bandwidth_cycle/1024;//KB
    $remain = $used_bandwidth_cycle - $max_bandwidth_limit;
    
    $yesterday = date('Y-m-01');
    $today = date('Y-m-d');
    
    if($remain>0){  
        //$remain = $remain/1024/1024;
        $remain = $remain/1024/50;
        $remain = floor($remain);
        // $yesterday = date('Y-m-d',strtotime(\Carbon\Carbon::yesterday()));
        //print_r('bandwisth->'.$userDetails->id.'--'.$remain.'=='.$max_bandwidth_limit);die;
        $prev_bandwidth = OverUsage::whereBetween('recorded_date',[$yesterday,$today])->where(['user_id'=>$user->id,'service'=>'bandwidth'])->sum('over_usage');
        $remain = $remain-$prev_bandwidth;
        $remain = floor($remain); 
        if($remain>=1){ 
            $this->createUsage($user,$remain,$plantype,"bandwidth");
            
        }
    } 
    //end bandwidth
    //caption
    $used_video_caption_minutes = $userDetails->caption_minutes;
    $remain_caption_minutes = $used_video_caption_minutes - $max_video_captions_limit;
    if($remain_caption_minutes>0){
        $prev_captions = OverUsage::whereBetween('recorded_date',[$yesterday,$today])->where(['user_id'=>$user->id,'service'=>'captions'])->sum('over_usage');
        $remain_caption_minutes = $remain_caption_minutes-$prev_captions;
        
         
        echo "<br>";
        $remain_caption_minutes = floor($remain_caption_minutes);
        if($remain_caption_minutes>0){
        $this->createUsage($user,$remain_caption_minutes,$plantype,"captions");
        }
    }
    //end caption

    //antipiracy
    //$max_antipiracy = $plan->attributes['antipiracy_limit'];
    $max_antipiracy = 5;
    $used_anti_piracy = $user->anti_piracy;
    $remain_anti_piracy = $used_anti_piracy - $max_antipiracy;
    if($remain_anti_piracy>0){
        //echo "<br>Pricaru";
        //echo $used_anti_piracy.'-'.$max_antipiracy; die;
        $prev_anti_piracy = OverUsage::whereBetween('recorded_date',[$yesterday,$today])->where(['user_id'=>$user->id,'service'=>'anti-piracy'])->sum('over_usage');
        $remain_anti_piracy = $remain_anti_piracy-$prev_anti_piracy;
        $remain_anti_piracy = floor($remain_anti_piracy);
        if($remain_anti_piracy>0){
            $this->createUsage($user,$remain_anti_piracy,$plantype,"anti-piracy");
        }
    }
    //end piracy
    
    //dynamic_watermark
    $max_dynamic_watermark_per_item = $plan->attributes['dynamic_watermark_limit'];
    $used_dynamic_watermark = $user->visual_sessions_count;
    $remain_dynamic_watermark = $used_dynamic_watermark - $max_dynamic_watermark_per_item;
    if($remain_dynamic_watermark>0){
        $prev_dynamic_watermark = OverUsage::whereBetween('recorded_date',[$yesterday,$today])->where(['user_id'=>$user->id,'service'=>'dynamic-watermark'])->sum('over_usage');
        $remain_dynamic_watermark = $remain_dynamic_watermark-$prev_dynamic_watermark;
        $remain_dynamic_watermark = floor($remain_dynamic_watermark);
        if($remain_dynamic_watermark>1){
            $this->createUsage($user,$remain_dynamic_watermark,$plantype,"dynamic-watermark");
        }
    }
    //end dynamic_watermark 

    //forensic_watermark
    $max_forensic_watermark_per_item = $plan->attributes['forensic_watermark_limit'];
    $used_forensic_watermark = $user->forensic_sessions_count;
    $remain_forensic_watermark = $used_forensic_watermark - $max_forensic_watermark_per_item;
    if($remain_forensic_watermark>0){
        $prev_forensic_watermark = OverUsage::whereBetween('recorded_date',[$yesterday,$today])->where(['user_id'=>$user->id,'service'=>'dynamic-watermark'])->sum('over_usage');
        $remain_forensic_watermark = $remain_forensic_watermark-$prev_forensic_watermark;
        $remain_forensic_watermark = floor($remain_forensic_watermark);
        if($remain_forensic_watermark>1){
            $this->createUsage($user,$remain_forensic_watermark,$plantype,"forensic-watermark");
        }
    }
    //end forensic_watermark

    //contacts_enrichment
    $max_contacts_enrichment_per_item = $plan->attributes['contacts_enrichment_limit'];
    $used_contacts_enrichment = $user->enrich;
    $remain_contacts_enrichment = $used_contacts_enrichment - $max_contacts_enrichment_per_item;
    if($remain_contacts_enrichment>0){
        $prev_contacts_enrichment = OverUsage::whereBetween('recorded_date',[$yesterday,$today])->where(['user_id'=>$user->id,'service'=>'forensic-watermark'])->sum('over_usage');
        $remain_contacts_enrichment = $remain_contacts_enrichment-$prev_contacts_enrichment;
        $remain_contacts_enrichment = floor($remain_contacts_enrichment);
        if($remain_contacts_enrichment>1){
            $this->createUsage($user,$remain_contacts_enrichment,$plantype,"forensic-watermark");
        }
    }
    //end contacts_enrichment

    //translation_captions
    // $max_translation_captions_per_item = $plan->attributes['translation_captions_limit'];
    $max_translation_captions_per_item = 10;
    $used_translation_captions = $user->translation_minutes;
     $remain_translation_captions = $used_translation_captions - $max_translation_captions_per_item;
    if($remain_translation_captions>0){
        $prev_translation_captions = OverUsage::whereBetween('recorded_date',[$yesterday,$today])->where(['user_id'=>$user->id,'service'=>'translations'])->sum('over_usage');
        $remain_translation_captions = $remain_translation_captions-$prev_translation_captions;
        $remain_translation_captions = floor($remain_translation_captions);
        if($remain_translation_captions>1){
            $this->createUsage($user,$remain_translation_captions,$plantype,"translations");
        }
    }
    //end contacts_enrichment
}
}
}catch (Exception $e) {

}
    
}
function createUsage($user,$qty,$itemId,$service){
     die;
    $subscription_id = $user->currentPlan->id; 
    $itemId = "$itemId-$service";
    $subscriptionItem = SubscriptionItem::where(['stripe_plan'=>$itemId,'user_id'=>$user->id])->first();
    if($subscriptionItem){
      $stripe = new StripeHelper();
    $res = $stripe->createUsageRecord($subscriptionItem->stripe_id,['quantity' => $qty, 'timestamp' => Carbon::now()->timestamp]);
      info($res['result']->id);
      $resId = isset($res['result']->id)?$res['result']->id:null;
      info(json_encode($service));
      info(json_encode($qty));
     $this->recordUsage($user,$itemId,$qty,$service,$resId);
}
      //$this->recordUsage($user,$itemId,$qty);
     
}
function recordUsage($user,$itemId,$qty,$service,$resId){
    $OverUsage = OverUsage::create(['user_id'=>$user->id,'stripe_id'=>$itemId,'over_usage'=>$qty,'service'=>$service,'stripe_usage_id'=>$resId,'recorded_date'=>\Carbon\Carbon::now()]);
}
}