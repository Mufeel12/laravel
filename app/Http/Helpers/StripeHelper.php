<?php


namespace App\Http\Helpers;
use \DrewM\MailChimp\MailChimp;

class StripeHelper
{
    protected $client;
    protected $activeccampaign;
    protected $mailchimp;
	public function __construct()
	{
        $this->client = new \GuzzleHttp\Client(['headers' =>['Authorization' => "Bearer ".config('services.stripe.secret')]]);
        $this->activeccampaign = new \GuzzleHttp\Client(['headers' =>['Api-Token' => config('services.integrations.active_campaign.api_key'),'Content-Type' => 'application/json']]);
        $this->mailchimp = new MailChimp(config('services.integrations.mail_chimp.api_key'));
		 

    }
    
    function get($enpoint){
        try{
        $res = $this->client->request('GET', $endpoint);
        $statusCode = $response->getStatusCode();
        $content = $response->getBody();
        if($statusCode==200 || $statusCode==201){
            return ['type'=>'success','result'=>json_decode($content)];
        }else{
            return ['type'=>'error','result'=>json_decode($content)]; 
        }
       } catch (\Guzzle\Http\Exception\ConnectException $e) {
        $response = (string)$e->getResponse()->getBody();
     }
    } 
    public function createUsageRecord($stripe_id,$params){
        try{
        $response = $this->client->request('POST', "https://api.stripe.com/v1/subscription_items/$stripe_id/usage_records",[ 'form_params' => $params]);
        $statusCode = $response->getStatusCode();
        $content = $response->getBody();
        if($statusCode==200 || $statusCode==201){
            return ['type'=>'success','result'=>json_decode($content)];
        }else{
            return ['type'=>'error','result'=>json_decode($content)]; 
        }
       } catch (\Stripe\Exception\CardException $e) {
        $response = (string)$e->getResponse()->getBody();
        return ['type'=>'error','result'=>json_decode($response)]; 
     }
    }

    public function deleteSubscriptionItem($items){
                 foreach($items as $item){ 
                //    $response = $this->client->request('GET', "https://api.stripe.com/v1/subscription_items/si_HcGlaXdnhguWlR");  
                    $response = $this->client->request('DELETE', "https://api.stripe.com/v1/subscription_items/$item",[ 'form_params' => ['clear_usage'=>'true','proration_behavior'=>'create_prorations']]); 
            } 

    }
    public function cancelSubscriptionItem($item){
                info('cancdelitem-'.$item);
                $response = $this->client->request('DELETE', "https://api.stripe.com/v1/subscriptions/$item");

    }

    public function payInvoice($subscription,$item){
        $invoice_id = $item['invoice_id'];
        $payment_method = $item['payment_method']; 
        $this->createSource($subscription,$item['payment_method']);
         
        $response = $this->client->request('POST', "https://api.stripe.com/v1/invoices/$invoice_id/pay",[ 'form_params' => ['source'=>$payment_method['id']]]);
    }
    public function createSource($user,$token){
      
         
        // $response = $this->client->request('POST', "https://api.stripe.com/v1/payment_methods",[ 'form_params' => [
        //     'type'=>'card',
        //         'card'=>[
        //             'exp_month'=>$token['exp_month'],
        //             'exp_year'=>$token['exp_year'],
        //             'number'=>$token['card_number'],
        //             'cvc'=>$token['cvc'],
        //         ]
        //         ]
        //         ]); 
                 $response = $this->client->request('POST', "https://api.stripe.com/v1/sources",[ 'form_params' => [
                    'token'=>$token['id'],
                    'customer'=>$user->stripe_id,
                ]
                ]);
        // info(json_encode($response));die;
    }

    function customerPaid($custId){
        
        return ['type'=>'success','total'=>0];
        try{
            info($custId);
            $response = $this->client->request('GET', "https://api.stripe.com/v1/payment_intents",[ 'form_params' => [
            'customer'=>$custId,
            'limit'=>100
        ]
        ]);  
        $totalPaid = 0;
        $statusCode = $response->getStatusCode();
        $content = $response->getBody();
        if($statusCode==200 || $statusCode==201){
            $content = json_decode($content);
            foreach($content->data as $val){
                $totalPaid = $totalPaid+(float)$val->amount/100;
            }
            return ['type'=>'success','total'=>$totalPaid];
        }else{
            $totalPaid = 0;
            $content = json_decode($content);
            foreach($content->data as $val){
                $totalPaid = $totalPaid+(float)$val->amount/100;
            }
            return ['type'=>'error','result'=>$totalPaid,'res'=>json_decode($content)]; 
        }
       } catch (\Guzzle\Http\Exception\ConnectException $e) {
        $response = (string)$e->getResponse()->getBody();
         }catch (\Guzzle\Http\Exception\ClientException $e) {
        $response = (string)$e->getResponse()->getBody();
         }
        
        return $response;
    }
/**/
    function addMemberToActiveCapaign($email,$tags,$firstName,$lastName){
        $req = ['contact'=>['email'=>$email]];
        if($firstName!=''){
            $req['contact']['firstName'] = $firstName;
        } if($lastName!=''){
            $req['contact']['lastName'] = $lastName;
        }
        info('activecampaign--'.json_encode($req));
        $response = $this->activeccampaign->request('POST',"https://bigcommand.api-us1.com/api/3/contact/sync",['body'=>json_encode($req)]);
        // $response = $this->activeccampaign->get("https://bigcommand.api-us1.com/api/3/contacts");
        $data = json_decode($response->getBody(), true);
        $id = $data['contact']['id'];
        $this->addContactTolist($id);
        $this->listTags($id,$tags);
         
    }
    function activeCampaignAllTags($offset,$limit){
        
        if($offset!=null){
            $response = $this->activeccampaign->request('get',"https://bigcommand.api-us1.com/api/3/tags?limit=$limit&offset=$offset");
        }else{
            $response = $this->activeccampaign->request('get',"https://bigcommand.api-us1.com/api/3/tags?limit=$limit");
        }
        return $response;
    }
    function listTags($id,$tags){
        $response = $this->activeCampaignAllTags(null,100);
        // $response = $this->activeccampaign->get("https://bigcommand.api-us1.com/api/3/contacts");
        $data = json_decode($response->getBody(), true);
        $alltags = $data['tags'];
        //info('campaign-tag-'.json_encode($alltags));
        $totals = $data['meta']['total'];
        if($totals>100){
            $response = $this->activeCampaignAllTags(100,100);
             
            $data = json_decode($response->getBody(), true);
            $alltags = array_merge($alltags,$data['tags']);
        }
        $tagnames = [];
        $aloneTags = [];
        $existingTag = [];
        //info('request-tag-'.json_encode($tags));
        info('campaign-tag-'.json_encode($alltags));
        if($tags!=null && $alltags!=null){
            foreach($alltags as $val){
                $tagnames[]=$val['tag'];
            }
            foreach($tags as $val){
                if(!in_array($val,$tagnames)){
                    $aloneTags[] = $val;
                }
                if(in_array($val,$tagnames)){
                    $existingTag[] = $val;
                }
            }
            //info(json_encode($alltags)); 
            foreach($alltags as $val){
                if(in_array($val['tag'],$existingTag)){
                    info(json_encode($val['id'])); 
                    $this->assignTag($val['id'],$id);
                }
            }
      }else{
        $aloneTags = $tags;
      }
      //info('aloneTags-tag-'.json_encode($aloneTags));
        if($aloneTags!=null){
            foreach($aloneTags as $val){
                 $tagId = $this->createTag($val);
                 $this->assignTag($tagId,$id);
            }
        }
    }

    function assignTag($tagId,$id){
        $req = ['contactTag'=>['contact'=>$id,'tag'=>$tagId]];
        $response = $this->activeccampaign->request('POST',"https://bigcommand.api-us1.com/api/3/contactTags",['body'=>json_encode($req)]);
    }

    function createTag($name){
        $req = ['tag'=>['tag'=>$name,"tagType"=> "contact"]];
        $response = $this->activeccampaign->request('POST',"https://bigcommand.api-us1.com/api/3/tags",['body'=>json_encode($req)]);
        $data = json_decode($response->getBody(), true);
       
        return $data['tag']['id'];
    }
    function addContactTolist($contactId){
        $req = ['contactList'=>['contact'=>$contactId,'list'=>2,'status'=>1]];
        $response = $this->activeccampaign->request('POST',"https://bigcommand.api-us1.com/api/3/contactLists",['body'=>json_encode($req)]);
        $data = json_decode($response->getBody(), true);
        info('addContactTolist');
        info(json_encode($data));
        //return $data['tag']['id'];
    }
/***/

    function createSubscriptionSchedule($stripe_request){

        try{
            info('createSubscriptionSchedule');
            $response = $this->client->request('POST', "https://api.stripe.com/v1/subscription_schedules",[ 'form_params' => $stripe_request]);
            $statusCode = $response->getStatusCode();
            $content = $response->getBody();
            if($statusCode==200 || $statusCode==201){
                return ['type'=>'success','result'=>json_decode($content)];
            }else{
                return ['type'=>'error','result'=>json_decode($content)]; 
            }
           } catch (\Stripe\Exception\CardException $e) {
            $response = (string)$e->getResponse()->getBody();
            return ['type'=>'error','result'=>json_decode($response)]; 
         }
    }

    function mailChimpAddMember($email,$tags,$merge_fields){
        $list_id = '73c621cef9';
        $subscriber_hash = MailChimp::subscriberHash($email);
         
        $first_name = '';
        $last_name = '';
        if(isset($merge_fields['FNAME'])){ 
            $first_name = $merge_fields['FNAME'];
        }
        if(isset($merge_fields['LNAME'])){ 
            $last_name = $merge_fields['LNAME'];
        } 
        info('mailchimp--444'.json_encode([
            'email_address' => $email,
            'tags'=> json_decode($tags,true),
            'status'        => 'subscribed',
            'merge_fields' => $merge_fields
        ]));
        $result = $this->mailchimp->put("lists/$list_id/members/$subscriber_hash", [
            'email_address' => $email,
            'tags'=> json_decode($tags,true),
            'status'        => 'subscribed',
            'merge_fields' => (object)$merge_fields
        ]);
        $tags1 = json_decode($tags,true);
        $tags2 = [];
        if(count($tags1)>0){
            foreach($tags1 as $val){
                $tags2[] = ['name'=>$val,'status'=>'active'];
            }
        }
        $subscriber_hash = MailChimp::subscriberHash($email);
        $result = $this->mailchimp->post("lists/$list_id/members/$subscriber_hash/tags", [
            'tags'=> $tags2
        ]);
        //$stripe = new StripeHelper();
        $this->addMemberToActiveCapaign($email, json_decode($tags,true),$first_name,$last_name);
        return $result;
    }
}