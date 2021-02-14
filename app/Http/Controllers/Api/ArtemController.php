<?php // command line:: php artisan make:controller Api/ArtemController
namespace App\Http\Controllers\Api;
use App\OauthUser;
use App\Stage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

use TwitterOAuth\Auth\SingleUserAuth;
use TwitterOAuth\Serializer\ArraySerializer;

class ArtemController extends Controller
{
    public function index() {
        return view('oauth');
    }
    public function remove_oauth($name_service_user){
        try{
            $user = Auth::user();
            $oauth = OauthUser::where('name_user',$name_service_user)//name_service_user
                   ->where('user_id', $user->id)
                   ->first();
            OauthUser::find($oauth->id)->delete();
            echo 'Successfully';
        }catch (\Exception $ex) {
            echo 'Error ' . $ex->getMessage();
        }
    }
    
    public function get_id_twitter(){
        try{
            date_default_timezone_set('UTC');
            $credentials = array(
                'consumer_key' => 'XMpPc9TvVMudpHoKtiF9eWWk9',
                'consumer_secret' => '1Ic4tvbNkiQ661wiPuy8bgAUgaInqnBWpSOzorfQfT5DQDfK7s',
            );
            
            $serializer = new ArraySerializer();
            $auth = new SingleUserAuth($credentials, $serializer);
            $params = array(
                'oauth_callback' => '',
            );
            $response = $auth->post('oauth/request_token', $params);
            return redirect("https://api.twitter.com/oauth/authorize?oauth_token=".$response['oauth_token']);
        }catch (\Exception $ex) {
            echo 'Error ' . $ex->getMessage();
        }
    }
    
    public function oauth($service, Request $request){
        try{
            $code = Input::get('code');
            $client = new \GuzzleHttp\Client();
            $access_token = '';
            $id_service = '';
            switch($service){
            case "google":
                $response = $client->request('POST', 'https://www.googleapis.com/oauth2/v4/token', ['body' =>json_encode([
                    'code'=>$code,
                    'client_id'=>'39926243377-ib0b0qcqcqq4kqvsg50q8se5mc1rgcdl.apps.googleusercontent.com',
                    'client_secret'=>'jErZOYFnWzoeghUB90Lq8gMH',
                    'grant_type'=>'authorization_code',
                    'redirect_uri'=> config('env.ROOT_URL') . '/oauth/google',
                ])
                ]);
                $access_token = json_decode($response->getBody()->getContents(), true)['access_token'];
                $response = $client->request('GET', 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token='. $access_token);
                $id_service = json_decode($response->getBody()->getContents(), true)['id'];
                break;
            case "getresponse":
                $response = $client->request('POST', 'https://api.getresponse.com/v3/token',
                                             ['form_params' =>[
                                                 'code'=>$code,
                                                 'grant_type'=>'authorization_code',
                                                 'redirect_uri'=>config('env.ROOT_URL') . '/oauth/getresponse',
                                             ],
                                              'auth'=>['3ebeff9f-f180-11e9-bb53-f04da2754d84','b03414d64e9cc2b584a4a8fa64ee7f48ede72792']
                                             ]);
                $access_token = json_decode($response->getBody()->getContents(), true)['access_token'];
                $response = $client->request('GET', 'https://api.getresponse.com/v3/accounts',
                                             ['headers' => [
                                                 'Content-Type'=>'application/json;charset=utf-8',
                                                 'Authorization'=>'Bearer '.$access_token
                                             ]]);
                $id_service = json_decode($response->getBody()->getContents(), true)['accountId'];
                echo $access_token.' | '.$id_service;
                break;
            case "gotowebinar":
                $response = $client->request('POST', 'https://api.getgo.com/oauth/v2/token', ['form_params' =>[
                    'code'=>$code,
                    'grant_type'=>'authorization_code'
                ],
                                                                                              'headers'=>['Content-Type' => 'application/json;charset=utf-8',
                                                                                                          'Authorization'=>'Basic MFJPUkt2NUtIY0hqRzRSZ1ZvckdUMGdwNHBPUUNLV2E6aWlJYUdSQmtVTFE2YlR1Sg==',
                                                                                                          'Accept'=>'application/json',
                                                                                                          'Content-Type'=>'application/x-www-form-urlencoded']
                ]);
                $bb = json_decode($response->getBody()->getContents(), true);
                $access_token = $bb['access_token'];
                $id_service = $bb['account_key'];
                break;
            case "zoom":
                $response = $client->request('POST', 'https://zoom.us/oauth/token', ['form_params' =>[
                    'grant_type'=>'authorization_code',
                    'code'=>$code,
                    'redirect_uri'=>config('env.ROOT_URL') . '/oauth/zoom'
                ],
                                                                                     'headers'=>['Authorization' => 'Basic XzhWWUxvb2tSbmFiZ0hHZEg5dTBvQTpFbk11aVAzSDNlRGtQSjZhbDlsd2J2blNqSTJQQnp4dw==',
                                                                                                 'Content-Type'=>'application/x-www-form-urlencoded']
                ]);
                $access_token = json_decode($response->getBody()->getContents(), true)['access_token'];
                $response = $client->request('GET', 'https://api.zoom.us/v2/users/me?access_token='.$access_token,
                                             ['headers' => [
                                                 'Content-Type'=>'application/x-www-form-urlencoded'
                                             ]]);
                $id_service = json_decode($response->getBody()->getContents(), true)['id'];
                break;
            case "zoho":
                $response = $client->request('POST', 'https://accounts.zoho.com/oauth/v2/token', ['form_params' =>[
                    'grant_type'=>'authorization_code',
                    'client_id'=>'1000.PFA73ODT2X92NOOSKIHUBCHDO0X1JH',
                    'client_secret'=>'01c0ffd8c406259ce60ec6642123ad47bcf1f22221',
                    'redirect_uri'=>config('env.ROOT_URL') . '/oauth/zoho',
                    'code'=>$code
                ]
                ]);
                $access_token = json_decode($response->getBody()->getContents(), true)['access_token'];
                $response = $client->request('GET', 'https://www.zohoapis.com/crm/v2/users', ['headers' =>
                                                                                              ['Authorization' => 'Zoho-oauthtoken '.$access_token]
                ]);
                $id_service = json_decode($response->getBody()->getContents(), true)['users'][0]['id'];
                break;
            case "hubspot":
                $response = $client->request('POST', 'https://api.hubapi.com/oauth/v1/token', ['form_params' =>[
                    'grant_type'=>'authorization_code',
                    'client_id'=>'c491fbcc-19f4-4b32-ae85-7c780eb6be34',
                    'client_secret'=>'e21605bb-7da2-459d-b7c8-aad084d83300',
                    'redirect_uri'=>config('env.ROOT_URL') . '/oauth/hubspot',
                    'code'=>$code
                ]
                ]);
                $access_token = json_decode($response->getBody()->getContents(), true)['access_token'];
                $response = $client->request('GET', 'https://api.hubapi.com/oauth/v1/access-tokens/'. $access_token);
                $bb=json_decode($response->getBody()->getContents(), true);
                $id_service = $bb["user_id"];
                break;
            case "mailchimp":
                $response = $client->request('POST', 'https://login.mailchimp.com/oauth2/token', ['form_params' =>[
                    'code'=>$code,
                    'client_id'=>'185587856889',
                    'client_secret'=>'86bbf4a555d797c1d55b916e85b9c554001847290857e94614',
                    'grant_type'=>'authorization_code',
                    'redirect_uri'=>config('env.ROOT_URL') . '/oauth/mailchimp',
                ]
                ]);
                $access_token = json_decode($response->getBody()->getContents(), true)['access_token'];
                $response = $client->request('GET', 'https://login.mailchimp.com/oauth2/metadata', ['headers' => ['Authorization' => 'OAuth '.$access_token]]);
                $id_service = json_decode($response->getBody()->getContents(), true)['user_id'];
                break;
            case "facebook":
                $response = $client->request('GET', 'https://graph.facebook.com/v5.0/oauth/access_token?'
                                             .'&client_id=1623916221079175'
                                             .'&client_secret=26e2c006155d8d26fa56fccded5ba23d'
                                             .'&redirect_uri='.config('env.ROOT_URL').'/oauth/facebook'
                                             .'&code='.$code);
                $access_token = json_decode($response->getBody()->getContents(), true)['access_token'];
                $response = $client->request('GET', 'https://graph.facebook.com/me?fields=id,name&access_token='.$access_token);
                $id_service = json_decode($response->getBody()->getContents(), true)['id'];
                break;
            case "linkedin":
                //grant_type=client_credentials&client_id=93r29maplxr58u&client_secret=rA1z8zBOM3yrX123
                $response = $client->request('POST', 'https://www.linkedin.com/oauth/v2/accessToken?'
                                             .'&grant_type=authorization_code'
                                             .'&code='.$code
                                             .'&redirect_uri='.config('env.ROOT_URL').'/oauth/linkedin'
                                             .'&client_id=26e2c006155d8d26fa56fccded5ba23d'
                                             .'&client_secret=');
                $access_token = json_decode($response->getBody()->getContents(), true)['access_token'];
                $response = $client->request('GET', 'https://api.linkedin.com/v2/me?projection=(id,firstName,lastName)',
                                             ['headers' => [
                                                 'Authorization' => 'Bearer '.$access_token,
                                                 'X-RestLi-Protocol-Version' => '2.0.0',
                                             ]
                                             ]);
                $id_service = json_decode($response->getBody()->getContents(), true)['id'];
                break;
                
            case "twitter"://     http://127.0.0.1:8000/oauth/get/twitter
                $response = $client->request('POST', 'https://api.twitter.com/oauth/access_token', ['form_params' =>[
                    'oauth_verifier'=>Input::get('oauth_verifier'),
                    'oauth_consumer_key'=>'185587856889',
                    'oauth_token'=>Input::get('oauth_token')
                ]
                ]);
                $access_token = explode('&',explode('oauth_token=', $response->getBody())[1])[0];
                $id_service = explode('&',explode('user_id=', $response->getBody())[1])[0];
                break;
            case "aweber":
                $response = $client->request('POST', 'https://auth.aweber.com/oauth2/token',
                                             ['headers' => [
                                                 'Content-Type'=>'application/x-www-form-urlencoded',
                                                 'Authorization' => 'Basic ZXlvTFRzbjFtVWdHVXBhWWNOOWtMOHFpTXdNVFVrbkk6NFI0bmFkenZyUHhlc1ByTUVwQjZ4TUY0MW94UFRvdnY='
                                             ],
                                              'form_params' =>[
                                                  'grant_type'=>'authorization_code',
                                                  'code'=>$code,
                                                  'redirect_uri'=>config('env.ROOT_URL') . '/oauth/aweber'
                                              ]
                                             ]
                );
                $access_token = json_decode($response->getBody()->getContents(), true)['access_token'];
                $response = $client->request('GET', 'https://api.aweber.com/1.0/accounts',
                                             ['headers' => ['Authorization' => 'Bearer '.$access_token]]);
                $id_service = json_decode($response->getBody()->getContents(), true)['entries'][0]['id'];
                break;
            }
            
            
            //$header = $request->header('referer'); chang for name_service
            //Create get request
            if (Auth::check()){//Registration service, if user authorization
                $user = Auth::user();
                $data = [//User_id need write
                    'user_id' => $user->id,
                    'name_service' => $service,
                    'id_service' => $id_service,
                    'access_token' => $access_token,
                    'name_user' => $service,//name_service_user
                ];
                OauthUser::create($data);
                $redirect_uri = Input::get('redirect_uri', '/home/');
                return redirect($redirect_uri);
            }else{//user not authorization
                $oauth_user = OauthUser::where('id_service', $id_service)->where('name_service',$service)->first();
                $redirect_uri = Input::get('redirect_uri', '/');
                if (isset($oauth_user)){
                    if (auth()->loginUsingId($oauth_user->user_id, true)) {
                        $tokenObject = $this->createTokenForUser(auth()->user());
                        $tokenObject->token->expires_at = now()->addDays(config('services.passport.expires_remember_me'));
                        $tokenObject->token->update([
                            'expires_at' => now()->addDays(config('services.passport.expires_remember_me'))
                        ]);
                        $user = auth()->user();
                        $this->setLoginActivity($request, $user);
                        Stage::createDefaultStage($user);
                        return redirect( $redirect_uri)
                            ->header('Authorization', 'Bearer ' . $tokenObject->accessToken)
                            ->header('access_token', $tokenObject->accessToken)
                            ->header('expires_in', $tokenObject->token->expires_at->diffInSeconds(now()))
                            ;
                    }
                }else{
                    //error with no code
                    return redirect('/login?error=oauth');
                }
            }
            return redirect('/login?error=oauth');
        }catch (\Exception $ex) {
            echo 'Error ' . $ex->getMessage();
        }
    }
    private function setLoginActivity(Request $request, $user)
    {
        $ip = $request->getClientIp();
        $geo_location = geoip()->getLocation($ip);
        $user->last_activity = now($user->settings->timezone);
        $user->login_country = $geo_location['iso_code'];
        $user->login_city = $geo_location['city'];
        $user->save();
    }
    private function createTokenForUser($user)
    {
        $timezone = $user->settings->timezone ? $user->settings->timezone : config('app.timezone');
        config(['app.timezone' => $timezone]);
        $tokenObject = $user->createToken('BCForWeb');
        $tokenObject->token->expires_at = now()->addHours(config('services.passport.expires_hours'));
        $tokenObject->token->update([
            'expires_at' => now()->addHours(config('services.passport.expires_hours'))
        ]);
        return $tokenObject;
    }
    public function save_code(Request $request){
        $js = json_decode($request->getContent(), true);
        $code = $js['code_service'];
        $name = $js['name_service'];
        $user = $js['user_id'];
        return '// commentde_service='.$code.'; name_service='.$name.'; user_id='.$user;
    }
}
