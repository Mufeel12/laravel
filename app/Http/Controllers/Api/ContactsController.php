<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contacts\ContactsRepository;
use App\Repositories\IntegrationRepository;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactsController extends Controller
{
    protected $contactRepo;
    protected $integrationRepo;

    public function __construct(ContactsRepository $contactsRepository, IntegrationRepository $integrationRepository)
    {
        $this->contactRepo = $contactsRepository;
        $this->integrationRepo = $integrationRepository;
    }

    /**
     * Get contacts list
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($request->has('query')) {
            $contacts = $this->contactRepo->model()->with('video')->where('team_id', $user->currentTeam()->id);
            $contacts = $this->ifLimitedRecords($user, $contacts);
            if ($request->has('search_str')) {
                $contacts->where(function ($q) use ($request) {
                    $q->where('firstname', 'like', '%' . $request->input('search_str') . '%')
                        ->orWhere('lastname', 'like', '%' . $request->input('search_str') . '%')
                        ->orWhere('email', 'like', '%' . $request->input('search_str') . '%');
                });
            }

            if ($request->has('order_by')) {
                $orderBy = $request->input('order_by');
                switch ($orderBy) {
                    case 'oldest':
                        $contacts->orderBy('created_at', 'asc');
                        break;
                    case 'ascending':
                        $contacts->orderBy('firstname', 'asc');
                        break;
                    case 'descending':
                        $contacts->orderBy('firstname', 'desc');
                        break;
                    default:
                        $contacts->orderBy('created_at', 'desc');
                        break;
                }
            }
            $contacts = $contacts->paginate($request->input('rows'));
        } else {
            $contacts = $this->contactRepo->model()->with('video')->where('team_id', $user->currentTeam()->id);
            $contacts = $this->ifLimitedRecords($user, $contacts);
            $contacts->orderBy('created_at', 'desc');
            $contacts = $contacts->paginate($request->input('rows'));
        }

        $contacts->map(function ($row) use ($user) {
            $row = $this->getContactOtherInfo($row, $row, $user);

            return $row;
        });

        return response()->json($contacts);
    }

    /**
     * get contact external information from fullcontact api.
     *
     * @param $subscriber
     * @param $row
     * @param $user
     * @return mixed
     */
    private function getContactOtherInfo($subscriber, $row, $user)
    {
        $subscriber = $this->integrationRepo->setContactOtherInfo($subscriber);
        $subscriber->avatar = (!is_null($row->photo_url) && !empty($row->photo_url)) ? $row->photo_url : url('img/no_person.svg');
        $subscriber->last_activity = time_elapsed_string($subscriber->updated_at, false, $user->settings->timezone);
        $subscriber->tags = (!is_null($row->tags) && !empty($row->tags)) ? json_decode($row->tags, true) : [];

        $subscriber->watched_data = $this->contactRepo->getWatchSumBySubscriber($subscriber);

        $subscriber->interests = !is_null($subscriber->interests) ? json_decode($subscriber->interests, true) : [];

        return $subscriber;
    }

    /**
     * Update Contact Information
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateContactInfo(Request $request)
    {
        $subscriber = $this->contactRepo->find($request->input('id'));
        $user = $request->user();

        if ($subscriber) {
            $subscriber->firstname = $request->input('firstname');
            $subscriber->lastname = $request->input('lastname');
            $subscriber->email = $request->input('email');
            $subscriber->phone_number = $request->input('phone_number');

            $subscriber->facebook_link = $request->input('facebook_link');
            if (is_null($subscriber->facebook_name) || empty($subscriber->facebook_name)) {
                $subscriber->facebook_name = $this->getSocialUserName($request->input('facebook_link'));
            }

            $subscriber->linked_in_link = $request->input('linked_in_link');
            if (is_null($subscriber->linked_in_name) || empty($subscriber->linked_in_name)) {
                $subscriber->linked_in_name = $this->getSocialUserName($request->input('linked_in_link'));
            }

            $subscriber->twitter_link = $request->input('twitter_link');
            if (is_null($subscriber->twitter_name) || empty($subscriber->twitter_name)) {
                $subscriber->twitter_name = $this->getSocialUserName($request->input('twitter_link'));
            }

            $subscriber->tags = !is_null($request->input('tags')) || !empty($request->input('tags')) ? json_encode($request->input('tags')) : '';
            $subscriber->updated_at = now($user->settings->timezone);

            $subscriber->updated_flag = 1;

            $subscriber->save();

            $subscriber = $this->getContactOtherInfo($subscriber, $subscriber, $user);

            $subscriber->contact_id = $request->input('id');
        }

        return response()->json([
            'result'  => 'success',
            'contact' => $subscriber
        ]);
    }

    /**
     * Get social username from given social profile url.
     *
     * @param String $social_link
     * @return String
     */
    private function getSocialUserName($social_link)
    {
        $path = parse_url($social_link, PHP_URL_PATH);
        if ($path && !is_null($path)) {
            $f_n_array = explode('/', $path);
            if (count($f_n_array) > 0) {
                return $f_n_array[(count($f_n_array) - 1)];
            } else {
                return $path;
            }
        }

        return '';
    }

    /**
     * Delete Subscriber
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteSubscriber(Request $request)
    {
        $this->contactRepo->model()->where('id', $request->input('contact_id'))->delete();

        return response()->json([
            'result' => 'success'
        ]);
    }

    public function getWatchedHistory(Request $request)
    {
        $id = $request->input('id');
        $query = $request->input('query');

        $history = $this->contactRepo->getWatchHistoryBySubscriber($id, $query);

        return response()->json($history);
    }

    public function getTagHistory(Request $request)
    {
        $id = $request->input('id');
        $query = $request->input('query');

        $tags = $this->contactRepo->getTagHistoryBySubscriber($id, $query);

        return response()->json($tags);
    }

    /**
     * getAutoTag lists
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getContactsAutoTags(Request $request)
    {
        $user = $request->user();

        $auto_tags = $user->auto_tags()->orderBy('created_at', 'desc')->get()->map(function ($el) use ($user) {
            $c_el = $el;

            $el->conditions = $c_el->conditions;

            $el->contact_count = $this->contactRepo->getContactCountsByCondition($user, $el->tag, $el->conditions, true);

            return $el;
        });

        return response()->json($auto_tags);
    }

    /**
     * Get videos list by user id
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllVideosByUserId(Request $request)
    {
        $user = $request->user();

        $videos = $this->contactRepo->getFullVideosListByUserId($user);

        return response()->json($videos);
    }

    /**
     * get filtered contacts count()
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getFilteredContactsCount(Request $request)
    {
        $user = $request->user();
        $tag = $request->input('tag');
        $conditions = $request->input('conditions');

        $contacts = $this->contactRepo->getContactCountsByCondition($user, $tag, $conditions);

        return response()->json($contacts);
    }

    /**
     * Save auto tag data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAutoTagsData(Request $request)
    {
        $user = $request->user();

        $id = $request->input('id');

        if ($id == '0' || !$id) {
            $check_duplicate_title = $this->contactRepo->auto_tag_model()
                ->where('user_id', $user->id)
                ->where('title', $request->input('title'))
                ->count();

            if ($check_duplicate_title > 0) {
                return response()->json([
                    'result' => 'duplicate_error'
                ]);
            }
            $this->contactRepo->createAutoTag($request, $user);
        } else {
            $check_duplicate_title = $this->contactRepo->auto_tag_model()
                ->where('user_id', $user->id)
                ->where('id', '<>', $id)
                ->where('title', $request->input('title'))
                ->count();

            if ($check_duplicate_title > 0) {
                return response()->json([
                    'result' => 'duplicate_error'
                ]);
            }

            $this->contactRepo->updateAutoTag($request, $user);
        }

        return response()->json([
            'result' => 'success'
        ]);
    }

    /**
     * delete auto tag.
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAutoTagsData(Request $request)
    {
        $id = $request->input('id');

        $this->contactRepo->auto_tag_model()->find($id)->delete();
        $this->contactRepo->auto_tag_condition_model()->where('auto_tag_id', $id)->delete();

        return response()->json([
            'result' => 'success'
        ]);
    }

    /**
     * deleteAutoTagCondition
     *
     * @param Request $request
     * @return JsonResponse
     * @throws
     */
    public function deleteAutoTagCondition(Request $request)
    {
        $id = $request->input('id');

        $this->contactRepo->find($id)->delete();

        return response()->json([
            'result' => 'success'
        ]);
    }

    /**
     * update active status of auto tags.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateActiveAutoTag(Request $request)
    {
        $id = $request->input('id');

        $autoTag = $this->contactRepo->auto_tag_model()->find($id);
        $active = $request->input('active');

        $autoTag->active = $active ? 1 : 0;
        $autoTag->save();

        return response()->json([
            'result' => 'success'
        ]);
    }

    protected function ifLimitedRecords($user, $contacts)
    {
        $plan = User::ownerPlan($user);
        if ($plan) {
            if ($plan->name == 'Free Forever') {
                $ids = $contacts->pluck('id')->toArray();
                $limitedIds = array_splice($ids, 0, 100);
                $contacts = $contacts->whereIn('id', $limitedIds);
            } elseif ($plan->name == 'Starter') {
                $ids = $contacts->pluck('id')->toArray();
                $limitedIds = array_splice($ids, 0, 5000);
                $contacts = $contacts->whereIn('id', $limitedIds);
            }
        }
        return $contacts;
    }
}
