<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Integrations\Contracts\IntegrationServices;
use App\Integration;
use App\Repositories\IntegrationRepository;
use App\Subscriber;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ActiveCampaign extends Controller implements IntegrationServices
{
    protected $integrationRepo;
    protected $client = null;
    protected $activeCampaign = false;

    public $service_key = 'activecampaign';

    public function __construct()
    {
        $this->integrationRepo = new IntegrationRepository();
        $this->client = new Client();
    }

    public function bootActiveCampaign($url, $key)
    {
        $this->activeCampaign = new \ActiveCampaign($url, $key);

        if (!(int)$this->activeCampaign->credentials_test()) {
            $this->activeCampaign = false;
        }

        return $this->activeCampaign;
    }

    /**
     * Connect to Active Campaign
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function connect(Request $request)
    {
        $url = $request->input('APIUrl');
        $api_key = $request->input('APIKey');
        $ac = $this->bootActiveCampaign($url, $api_key);

        if (!$ac) {
            return response()->json([
                'result'  => 'error',
                'message' => 'Invalid credentials (URL and/or API key)'
            ]);
        }

        $display_name = $request->input('display_name');
        $user = auth()->user();
        $integration = $user->integrations()->where('service_key', $this->service_key)->first();
        if ($integration) {
            $integration->display_name = $display_name;
            $integration->api_key = $api_key;
            $integration->service_url = $url;
            $integration->save();
        } else {
            $integration = $this->integrationRepo->create([
                'display_name' => $display_name,
                'service_name' => Integration::SERVICE_LIST[$this->service_key]['controller'],
                'service_key'  => $this->service_key,
                'api_key'      => $api_key,
                'service_url'  => $url
            ], $user);
        }

        return response()->json([
            'result'  => 'success',
            'message' => $integration
        ]);
    }

    /**
     * Get lists.
     * @return array
     */
    public function lists()
    {
        $integration = $this->integrationRepo->model()
            ->where('user_id', auth()->user()->id)
            ->where('service_key', $this->service_key)
            ->orderBy('created_at', 'desc')
            ->first();

        $lists = [];
        if ($integration) {
            $ac = $this->bootActiveCampaign($integration->service_url, $integration->api_key);
            if ($ac) {
                $list_params = [
                    'ids'  => 'all',
                    'full' => '0'
                ];

                $lists_val = $ac->api('list/list', $list_params);
                if ($lists_val) {
                    $i = 0;
                    foreach ($lists_val as $item) {
                        if (!$item || !isset($item->id)) {
                            continue;
                        }
                        $lists[$i] = [
                            'id'   => $item->id,
                            'name' => $item->name,
                        ];
                        $i++;
                    }
                }
            }
        }

        return [
            'lists' => $lists
        ];
    }

    /**
     * Subscribe to service
     *
     * @param Subscriber $subscriber
     * @param array $lists
     * @param null $tags
     * @return bool|string
     */
    public function subscribe(Subscriber $subscriber, $lists = [], $tags = null)
    {
        $integration = $this->integrationRepo->model()
            ->where('user_id', $subscriber->user_id)
            ->where('service_key', $this->service_key)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($integration) {
            $ac = $this->bootActiveCampaign($integration->service_url, $integration->api_key);
            if ($ac && $lists) {
                foreach ($lists as $list_id) {
                    $list_params = [
                        'ids'  => $list_id,
                        'full' => '0'
                    ];
                    $list = $ac->api('list/list', $list_params);

                    if ($list) {
                        $params = [
                            'email'                                 => $subscriber->email,
                            'first_name'                            => $subscriber->firstname,
                            'last_name'                             => $subscriber->lastname,
                            ('p[' . $list_id . ']')                 => $list_id,
                            ('status[' . $list_id . ']')            => 1,
                            ('instantresponders[' . $list_id . ']') => 1
                        ];

                        $ac->api('contact/add', $params);
                    }
                }

                $integration->updated_at = now();
                $integration->save();

                return 'success';
            }
        }

        return false;
    }
}
