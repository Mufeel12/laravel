<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 11.10.2015
 * Time: 9:12
 */

namespace App\Support\Adapters;


use App\Exceptions\CTAMonkeyException;
use App\Support\Models\Channel;
use App\Support\Models\Video;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Vinkla\Vimeo\VimeoManager;

//use Vimeo\Vimeo;

/**
 * Class VimeoImport
 * @package App\Support\Adapters
 */
class VimeoAdapter implements SearchVideoInterface
{
    /**
     * Vimeo client
     * @var VimeoAdapter
     */
    protected $client;

    /**
     * @var string
     */
    protected $name = 'vimeo';

    /**
     * Class constructor
     *
     * Inject Vimeo client
     *
     * @param VimeoAdapter $client
     * @throws CTAMonkeyException
     */
    public function __construct(VimeoAdapter $client = null)
    {

        // If no client, get client from container
        if (is_null($client)) {
            // THE SAME: Vimeo::connection(), but via facade
            $client = $client = app()['vimeo'];
        }

        // Throw an exception if client is ill.
        if (!$client instanceof VimeoAdapter && !$client instanceof VimeoManager) {
            throw new CTAMonkeyException('Given ' . get_class($client) . ' not instance of [Vimeo\Vimeo]');
        }

        $this->client = $client;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Search video
     *
     * @see: https://developer.vimeo.com/api/endpoints/videos
     * @param string $query
     * @param array $params
     * @return array
     */
    public function search($query, $params = [])
    {

        $query = $this->parseQuery($query);

        $client_params = [
            'query' => $query['value'],
            'per_page' => $params['max_results'],
            'sort' => $this->resolveOrder($params['order'])
        ];

        if (!empty($params['page']['vimeo'])) {
            $client_params['page'] = $params['page']['vimeo'];
        }

        if ($query['type'] === 'channel') {

            // Unset query from search paramenters
            unset($client_params['query']);

            // ERROR: The sort provided is not valid for this resource.
            unset($client_params['sort']);
            # todo: check if this works
            $channel = $this->channelInformation($query['value']);
            $results = $this->channelVideos($query['value'], $client_params);
            $results['type'] = 'channel';
            $results['channel'] = $channel;

            return $results;
        }

        if ($query['type'] === 'video') {
            $result = $this->getVideoById($query['value']);
            // need to set this for view
            $result->type = 'singleVideo';
            return $result;
        }

        if ($query['type'] === 'string') {

            $results = $this->searchVideo($client_params);

            return $results;
        }

        return [];
    }

    /**
     * Get single video by ID
     *
     * @param   string $videoId
     * @return  array
     */
    public function getVideoById($videoId)
    {
        $results = $this->client->request("/videos/{$videoId}");
        $data = $this->transformOutput($results);

        return $data;
    }

    /**
     * Search videos
     *
     * @link: https://developer.vimeo.com/api/endpoints/videos
     * @param  array $params
     * @return array
     */
    public function searchVideo($params)
    {
        $results = $this->client->request('/videos', $params);
        $data = $this->transformOutput($results);

        return new Collection([
            'next_page' => $results['body']['page'] + 1, // TODO: get next page from `paging` key
            'data' => $data
        ]);
    }

    /**
     * Search channel videos
     *
     * @link: https://developer.vimeo.com/api/endpoints/channels#/{channel_id}/videos
     * @param string $channel
     * @param string $params
     * @return array
     */
    public function channelVideos($channel, $params)
    {
        $results = $this->client->request("/channels/{$channel}/videos", $params);
        $data = $this->transformOutput($results);

        return new Collection([
            'next_page' => $results['body']['page'],
            'data' => $data
        ]);
    }

    /**
     * Returns channel information
     *
     * @param $channel
     * @return array
     */
    public function channelInformation($channel)
    {
        $channel = $this->client->request("/channels/{$channel}");

        // Format return a little bit so we OWN IT in view
        return new Channel([
            'picture' => (isset($channel['body']['pictures']['sizes'][0]['link']) ?
                $channel['body']['pictures']['sizes'][0]['link'] :
                last($channel['body']['pictures']['sizes'])['link']),
            'name' => $channel['body']['name'],
            'link' => $channel['body']['link'],
            'description' => $channel['body']['description']
        ]);
    }

    /**
     * Parse query sting and detect it's type
     *
     * @param $query
     * @return array
     */
    public function parseQuery($query)
    {

        /**
         * Little trick.
         * Check first for video id, next for channel id.
         * Url like http://vimeo.com/channels/vimeogirls/67019023 is video id, but channel too
         */

        if ($videoId = $this->getVideoIdFromUrl($query)) {
            return [
                'type' => 'video',
                'value' => $videoId
            ];
        }

        if ($channelId = $this->getChannelIdFromUrl($query)) {
            return [
                'type' => 'channel',
                'value' => $channelId
            ];
        }

        return [
            'type' => 'string',
            'value' => $query
        ];
    }


    /**
     * Get channel if from url
     *
     * TODO: need improve: https://regex101.com/r/tB1kW3/1
     *
     * http://vimeo.com/channels/vimeogirls/67019023 - this is an video. Should skip
     *
     * @param   string $url
     * @return  string|bool
     */
    public function getChannelIdFromUrl($url)
    {
        $regex = "/(?:https?:\/\/)?vimeo\.com\/channels\/(?<channel>[a-z0-9\-_]+)[\/?a-z-A-Z-0-9\-]?/";
        preg_match($regex, $url, $matches);

        return isset($matches['channel']) ? $matches['channel'] : false;
    }

    /**
     * Get video ID from url
     *
     * @param  string $url
     * @return string|bool
     */
    public function getVideoIdFromUrl($url)
    {
        $regex = '/(https?:\/\/)?(www.)?(player.)?vimeo.com\/([a-z]*\/)*(?<video_id>[0-9]{6,11})[?]?.*/';
        $url = preg_replace('/album\/[0-9]{6,11}\//', '', $url); // Replace string 'album/0101010/' by ''
        preg_match($regex, $url, $matches);

        return isset($matches['video_id']) ? $matches['video_id'] : false;
    }

    /**
     * Transform video data
     *
     * @param   array $data
     * @return  array
     */
    private function transformOutput($data)
    {

        // Vimeo return result as array for both: single or collection.
        // But collection paginated and has key 'data'
        if (isset($data['body']['data'])) {

            $results = new Collection();

            foreach ($data['body']['data'] as $item) {
                $results[] = $this->transformItem($item);
            }

            return $results;
        }

        return $this->transformItem($data['body']);
    }

    /**
     * Transform single video record
     *
     * @param $item
     * @return array
     */
    private function transformItem($item)
    {
        try {
            $date = Carbon::parse($item['created_time']);
        } catch (\Exception $e) {
            return false;
        }
        $thumbnail = $item['pictures']['sizes'][2]['link'];

        return new Video([
            'type' => 'video',
            'source' => 'vimeo',
            'path' => $item['link'],
            'title' => $item['name'],
            'video_id' => $this->getVideoIdFromUrl($item['link']),
            'date' => $date->format('Y-m-d H:i:s'),
            'human_date' => Carbon::parse($date)->diffForHumans(),
            'description' => truncate($item['description'], 180),
            'channel_url' => $item['user']['link'],
            'channel_title' => $item['user']['name'],
            'is_imported' => false,
            'duration' => $item['duration'],
            'duration_formatted' => gmdate('i:s', $item['duration']),
            'thumbnail' => $thumbnail,
            'views' => $item['stats']['plays']
        ]);
    }

    /**
     * @param $order
     * @return bool|string
     */
    private function resolveOrder($order)
    {
        /*
         * Acceptable youtube values are:
            date – Resources are sorted in reverse chronological order based on the date they were created.
            rating – Resources are sorted from highest to lowest rating.
            relevance – Resources are sorted based on their relevance to the search query. This is the default value for this parameter.
            title – Resources are sorted alphabetically by title.
            videoCount – Channels are sorted in descending order of their number of uploaded videos.
            viewCount – Resources are sorted from highest to lowest number of views.

            vimeo values:
            relevant
            date
            alphabetical
            plays
            likes
            comments
            duration
         */
        $orderArray = [
            'relevance' => 'relevant',
            'date' => 'date',
            'rating' => 'likes',
            'title' => 'alphabetical',
            'viewCount' => 'plays'
        ];
        return (isset($orderArray[$order]) ? $orderArray[$order] : false);

    }
}