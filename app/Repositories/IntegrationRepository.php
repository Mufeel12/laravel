<?php


namespace App\Repositories;


use App\Http\Controllers\Api\IntegrationsController;
use App\Integration;
use App\IntegrationList;
use App\Subscriber;
use App\User;
use Illuminate\Support\Facades\DB;

class IntegrationRepository
{
    protected $client;

    public function model()
    {
        return app(Integration::class);
    }

    /**
     * Get Integration lists - connected, not connected.
     * @param $user
     * @return array
     */
    public function getIntegrations($user)
    {
        $email = Integration::SERVICE_TYPE['email'];
        $webinar = Integration::SERVICE_TYPE['webinar'];
        $crm = Integration::SERVICE_TYPE['crm'];
        $social_media = Integration::SERVICE_TYPE['social_media'];
        $other = Integration::SERVICE_TYPE['other'];

        $connected = [];
        $email_connected = [];
        $webinar_connected = [];
        $crm_connected = [];
        $social_media_connected = [];
        $other_connected = [];

        $team_id = auth()->user()->current_team_id;
        $team_users = DB::table('team_users')->select('*')->where(['team_id' => $team_id])->pluck('user_id');
        $integrations = $this->model()->whereIn('user_id', $team_users)->orderBy('created_at', 'desc')->get();
//        $integrations = $user->integrations()->orderBy('created_at', 'desc')->get();

        foreach ($integrations as $integration) {
            $integration->integrated_at = date('M d, Y', strtotime($integration->created_at)) . ' at ' . date('g:i A', strtotime($integration->created_at));
            $integration->last_activity = time_elapsed_string($integration->updated_at, false, $user->settings->timezone);

            $connected[] = $integration;

            if (array_search($integration->service_key, $email) !== false) {
                $email_connected[] = $integration;
            }

            if (array_search($integration->service_key, $webinar) !== false) {
                $webinar_connected[] = $integration;
            }

            if (array_search($integration->service_key, $crm) !== false) {
                $crm_connected[] = $integration;
            }

            if (array_search($integration->service_key, $social_media) !== false) {
                $social_media_connected[] = $integration;
            }

            if (array_search($integration->service_key, $other) !== false) {
                $other_connected[] = $integration;
            }
        }

        return [
            'connected'     => array_values($connected),
            'not_connected' => array_values(array_diff(Integration::SERVICE_KEYS, $connected)),
            'email'         => ['connected' => array_values($email_connected), 'not_connected' => array_values(array_diff($email, $email_connected))],
            'webinar'       => ['connected' => array_values($webinar_connected), 'not_connected' => array_values(array_diff($webinar, $webinar_connected))],
            'crm'           => ['connected' => array_values($crm_connected), 'not_connected' => array_values(array_diff($crm, $crm_connected))],
            'social_media'  => ['connected' => array_values($social_media_connected), 'not_connected' => array_values(array_diff($social_media, $social_media_connected))],
            'other'         => ['connected' => array_values($other_connected), 'not_connected' => array_values(array_diff($other, $other_connected))]
        ];
    }

    /**
     * Integration Service Create
     * @param $data
     * @param $user
     * @return Integration
     */
    public function create($data, $user)
    {
        $integration = new Integration();

        $integration->fill($data);
        $integration->user_id = $user->id;
        $integration->save();

        return $integration;
    }

    /**
     * get lists all or by given service key
     * @param bool $service_key
     * @return array|mixed
     */
    public function lists($service_key = false)
    {
        $user = auth()->user();

        $lists = [];
        if ($service_key) {
            $integration = $this->model()
//                ->where('user_id', $user->id)
                ->where('service_key', $service_key)
                ->whereNotIn('service_key', Integration::SERVICE_TYPE['social_media'])
                ->first();

            if ($integration && $integration->lists) {
                $lists = unserialize($integration->lists->lists);
            }

            $lists = array_add($lists, 'mailer', $service_key);
        } else {
            $team_id = $user->current_team_id;
            $team_users = DB::table('team_users')->select('*')->where(['team_id' => $team_id])->pluck('user_id');
            $integrations = $this->model()->whereIn('user_id', $team_users)->get();

            if ($integrations) {
                $integrations->each(function ($index) use (&$lists) {
                    $lists[] = $this->lists($index->service_key);
                });
            }
        }

        return $lists;
    }

    /**
     * Refresh Mailing lists of integration services.
     * @param bool $service_key
     * @return array
     */
    public function refreshServices($service_key = false)
    {
        $user = auth()->user();
        $lists = [];

        if ($service_key) {
            // Single.
            $controller = Integration::getServiceController($service_key);
            $lists = $controller->lists();

            $this->updateLists($service_key, $lists);
        } else {
            // All
            $integrations = $user->integrations()
                ->whereNotIn('service_key', Integration::SERVICE_TYPE['social_media'])
                ->get();
            if ($integrations) {
                $controller = $integrations->map(function ($el) {
                    return Integration::getServiceController($el->service_key);
                });

                $controller->each(function ($el) use (&$lists) {
                    try {
                        $lists[] = $this->refreshServices($el->service_key);
                    } catch (\Exception $e) {
                        try {
                            $lists[] = $this->refreshServices($el->service_key);
                        } catch (\Exception $e) {
                            return null;
                        }

                        return null;
                    }
                });
            }
        }

        return $lists;
    }

    /**
     * Create Or Update Mailing Lists.
     * @param $service_key
     * @param $lists
     * @return bool
     */
    public function updateLists($service_key, $lists)
    {
        $user = auth()->user();
        if ($user) {
            $integration = $user->integrations()->where('service_key', $service_key)->first();
            if ($integration) {
                $integrationList = $integration->lists;
                if (!$integrationList) {
                    $integrationList = new IntegrationList();
                    $integrationList->user_id = $user->id;
                    $integrationList->integration_id = $integration->id;
                }

                $integrationList->lists = serialize($lists);
                $integrationList->save();
            }
        }

        return false;
    }

    /**
     * Lead Capture from stage or watching video.
     *
     * @param $userId
     * @param $teamId
     * @param $projectId
     * @param $videoId
     * @param $integrationProvider
     * @param $email
     * @param $unique_id
     * @param string $firstName
     * @param string $lastName
     * @param string $phone
     * @param string $location
     * @return mixed
     */
    public function leadCapture($userId, $teamId, $projectId, $videoId, $integrationProvider, $email, $unique_id, $firstName = '', $lastName = '', $phone = '', $location = '')
    {
        $user = User::find($userId);
        $tags = null;

        $subscriber = Subscriber::firstOrNew([
            'user_id'    => $user->id,
            'team_id'    => $teamId,
            'email'      => $email,
            'project_id' => $projectId,
            'video_id'   => $videoId,
            'user_agent' => $unique_id,
        ]);

        if ($firstName && $firstName != '') {
            $subscriber->firstname = $firstName;
        }

        if ($lastName && $lastName != '') {
            $subscriber->lastname = $lastName;
        }

        if ($phone && $phone != '') {
            $subscriber->phone_number = $phone;
        }

        if ($location && $location != '') {
            $subscriber->location = $location;
        }

        if ($user->settings->auto_tag_email_list) {
            $subscriber->tags = $user->settings->stage_tags;
            $tags = json_decode($user->settings->stage_tags, true);
        }

        $subscriber->updated_flag = 0;

        $subscriber->save();

        $subscriber = $this->setContactOtherInfo($subscriber);

        $provider = $integrationProvider['provider'];
        $list = $integrationProvider['list'];

        return IntegrationsController::subscribe($subscriber, $provider, [$list], $tags);
    }

    /**
     * get social contacts by fullcontact api
     * @param $email
     * @return mixed|null
     */
    public function getSocialContacts($email)
    {
        try {
            $cmd_str = 'curl -X POST ' . config('services.full_contact_api.url') . ' -H "Authorization: Bearer ' . config('services.full_contact_api.key') . '" -d "{\"email\": \"' . $email . '\"}"';
            exec($cmd_str, $output, $return_var);

            if ($return_var === 0) {
                return json_decode($output[0]);
            } else {
                return null;
            }
        } catch (\Exception $e) {
        }

        return null;
    }

    /**
     * Set contact info by fullcontact api
     * @param $subscriber
     * @return mixed
     */
    public function setContactOtherInfo($subscriber)
    {
        if (!$subscriber->updated_flag) {
            $social_data = $this->getSocialContacts($subscriber->email);

            if (!is_null($social_data)) {
                if (isset($social_data->avatar) && !is_null($social_data->avatar)) {
                    $subscriber->photo_url = $social_data->avatar;
                }

                if (is_null($subscriber->twitter_link) || empty($subscriber->twitter_link)) {
                    if (isset($social_data->twitter) && !is_null($social_data->twitter)) {
                        $subscriber->twitter_link = $social_data->twitter;
//                        $subscriber->twitter_name = $this->getSocialUserName($social_data->twitter);
                    }
                }

                if (is_null($subscriber->facebook_link) || empty($subscriber->facebook_link)) {
                    if (isset($social_data->facebook) && !is_null($social_data->facebook)) {
                        $subscriber->facebook_link = $social_data->facebook;
//                        $subscriber->facebook_name = $this->getSocialUserName($social_data->facebook);
                    }
                }

                if (is_null($subscriber->linked_in_link) || empty($subscriber->linked_in_link)) {
                    if (isset($social_data->linkedin) && !is_null($social_data->linkedin)) {
                        $subscriber->linked_in_link = $social_data->linkedin;
//                        $subscriber->linked_in_name = $this->getSocialUserName($social_data->linkedin);
                    }
                }

                if (is_null($subscriber->firstname) || is_null($subscriber->lastname) || empty($subscriber->firstname) || empty($subscriber->lastname)) {
                    if (isset($social_data->fullName) && !is_null($social_data->fullName)) {
                        $f_n_array = explode(' ', $social_data->fullName);
                        if (isset($f_n_array[1])) {
                            $subscriber->firstname = $f_n_array[0];
                            $subscriber->lastname = $f_n_array[1];
                        } else {
                            $subscriber->firstname = $social_data->fullName;
                        }
                    }
                }

                if (is_null($subscriber->job_title) || empty($subscriber->job_title)) {
                    if (isset($social_data->title) && !is_null($social_data->title)) {
                        $subscriber->job_title = $social_data->title;
                    }
                }

                if (is_null($subscriber->organization) || empty($subscriber->organization)) {
                    if (isset($social_data->organization) && !is_null($social_data->organization)) {
                        $subscriber->organization = $social_data->organization;
                    }
                }

                if (is_null($subscriber->website) || empty($subscriber->website)) {
                    if (isset($social_data->website) && !is_null($social_data->website)) {
                        $subscriber->website = $social_data->website;
                    }
                }

                if (is_null($subscriber->location) || empty($subscriber->location)) {
                    if (isset($social_data->location) && !is_null($social_data->location)) {
                        $subscriber->location = $social_data->location;
                    }
                }

                if (is_null($subscriber->interests) || empty($subscriber->interests)) {
                    if (isset($social_data->details->interests) && !is_null($social_data->details->interests)) {
                        $subscriber->interests = json_encode($social_data->details->interests);
                    }
                }

                if (is_null($subscriber->details) || empty($subscriber->details)) {
                    if (isset($social_data->details) && !is_null($social_data->details)) {
                        $subscriber->details = json_encode($social_data->details);
                    }
                }

                if (isset($social_data->details) && !is_null($social_data->details)) {
                    if (isset($social_data->details->gender) && !is_null($social_data->details->gender)) {
                        $subscriber->gender = $social_data->details->gender;
                    }
                }
            }
        }

        $subscriber->updated_flag = 1;

        $subscriber->save();

        return $subscriber;
    }
}
