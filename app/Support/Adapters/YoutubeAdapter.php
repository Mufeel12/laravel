<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 11.10.2015
 * Time: 9:12
 */

namespace App\Support\Adapters;

use App\Support\Models\Channel;
use App\Support\Models\Video;
use App\Support\Youtube\YoutubeClient;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Madcoda\Youtube\Youtube;

/**
 * Class YoutubeImport
 * @package App\Support\Adapters
 */
class YoutubeAdapter implements SearchVideoInterface
{

    /**
     * Youtube client
     * @var YoutubeAdapter
     */
    protected $client;

    /**
     * @var string
     */
    protected $name = 'youtube';

    /**
     * Class constructor
     *
     * @param YoutubeClient|null $client
     */
    public function __construct(YoutubeClient $client = null)
    {
        if (is_null($client)) {
            $client = self::make();
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
     * Search videos
     *
     * @param $query
     * @param array $params
     * @return array
     */
    public function search($query, $params)
    {

        $query = $this->parseQuery($query);

        // Channel
        if ($query['type'] === 'channel') {
            $channel = $this->getChannel($query['value']);
            $results = $this->getChannelVideos($query['value']);
            $results['type'] = 'channel';
            $results['channel'] = $channel;

            return $results;
        }

        // User
        if ($query['type'] === 'user') {
            $channel = $this->client->getChannelByName($query['value']);
            $results = $this->getChannelVideos($channel->id, $params['max_results']);

            return $results;
        }

        // Single video url
        if ($query['type'] === 'video') {
            $data = $this->getVideoById($query['value']);
            $result = $this->transformItem($data);
            $result->type = 'singleVideo';
            return $result;
        }

        if ($query['type'] === 'string') {

            $search_params = [
                'order' => $params['order'],
                'type' => 'video',
                'part' => 'id, snippet',
                'maxResults' => $params['max_results'],
                'pageToken' => $params['page']['youtube']
            ];

            return $this->searchVideos($query['value'], $search_params);
        }

        return [];

    }

    /**
     * Search videos
     *
     * @param $query
     * @param $params
     * @return \StdClass
     */
    public function searchVideos($query, $params)
    {

        $search_params = array_merge([
            'q' => $query,
        ], $params);

        $results = $this->client->searchAdvanced($search_params, true);

        $ids = $this->extractVideoIds($results['results']);
        $infos = $this->client->getVideosInfo($ids);
        $results['results'] = $this->mergeInfo($results['results'], $infos);
        $data = $this->transformOutput($results['results']);

        return collect([
            'next_page' => $results['info']['nextPageToken'],
            'data' => $data
        ]);
    }

    /**
     * Get channel videos by ID
     *
     * @param $channelId
     * @param int $max_results
     * @return \StdClass
     */
    public function getChannelVideos($channelId, $max_results = 10)
    {
        $results = $this->client->searchChannelVideos('', $channelId, $max_results);

        /**
         * After processing
         */
        $ids = $this->extractVideoIds($results);
        $infos = $this->client->getVideosInfo($ids);
        $results = $this->mergeInfo($results, $infos);
        $data = $this->transformOutput($results);

        return collect([
            'next_page' => false,
            'data' => $data
        ]);
    }

    /**
     * Returns channel information
     *
     * @param $channelId
     * @return array
     */
    public function getChannel($channelId)
    {
        $channel = $this->client->getChannelById($channelId);

        return new Channel([
            'name' => $channel->snippet->title,
            'description' => $channel->snippet->description,
            'link' => 'https://youtube.com/channel/' . $channel->id,
            'picture' => $channel->snippet->thumbnails->default->url,
        ]);
    }

    /**
     *
     * Get video by ID
     *
     * @param $videoId
     * @return \StdClass
     */
    public function getVideoById($videoId)
    {
        return $this->client->getVideoInfo($videoId);
    }

    /**
     * Make Youtube API client
     *
     * @return YoutubeClient
     */
    public static function make()
    {
        return new YoutubeClient(app()['config']->get('youtube'));
    }

    /**
     * Parse search query
     *
     * PArse search query and get channel Id of video Id if $query is youtube url
     *
     * @param $query
     * @return array|bool
     */
    public function parseQuery($query)
    {
        if ($channel = $this->getChannelIdFromURL($query)) {
            return $channel;
        }

        if ($videoId = $this->getVideoIdFromUrl($query)) {
            return [
                'type' => 'video',
                'value' => $videoId
            ];
        }

        return [
            'type' => 'string',
            'value' => $query
        ];
    }

    /**
     * Get channel from url
     *
     * @param $url
     * @return bool|object
     */
    private function getChannelIdFromURL($url)
    {

        if (strpos($url, 'youtube.com') === false) {
            return false;
        }

        $path = Youtube::_parse_url_path($url);

        if (strpos($path, '/channel') === 0) {
            $segments = explode('/', $path);
            $channelId = $segments[count($segments) - 1];

            return [
                'type' => 'channel',
                'value' => $channelId
            ];
        }

        if (strpos($path, '/user') === 0) {
            $segments = explode('/', $path);
            $username = $segments[count($segments) - 1];

            return [
                'type' => 'user',
                'value' => $username
            ];
        }

        return false;

    }

    /**
     * Extract video ids from result array
     *
     * @param $result
     * @return array
     */
    private function extractVideoIds($result)
    {
        return array_pluck($result, 'id.videoId');
    }

    /**
     * Get video ID from url
     *
     * @param $url
     * @return bool|string
     */
    public function getVideoIdFromUrl($url)
    {
        try {

            // TODO: BAD! Not parse if any other argument in url
            $videoId = Youtube::parseVIdFromURL($url);

            return $videoId;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Merge search results with video infos
     *
     * @param object $results
     * @param object $infos
     * @return object
     */
    private function mergeInfo($results, $infos)
    {
        foreach ($results as $key => &$value) {
            $value->contentDetails = (isset($infos[$key]->contentDetails) ? $infos[$key]->contentDetails : '');
            $value->status = (isset($infos[$key]->status) ? $infos[$key]->status : '');
            $value->statistics = (isset($infos[$key]->statistics) ? $infos[$key]->statistics : '');
        }

        return $results;
    }

    /**
     * @param $data
     * @return array
     */
    private function transformOutput($data)
    {
        // If array
        if (is_array($data)) {

            $results = collect();

            foreach ($data as $item) {
                $results[] = $this->transformItem($item);
            }

            return $results;
        }

        return $this->transformItem($data);

    }

    /**
     * Transform single item
     *
     * @param $item
     * @return array
     */
    private function transformItem($item)
    {
        $video_id = (isset($item->id->videoId) ? $item->id->videoId : (isset($item->id) ? $item->id : ''));
        $duration = $item->contentDetails->duration;

        /**
         * convert youtube duration
         */
        $time = new \DateTime('@0'); // Unix epoch
        $time->add(new \DateInterval($duration));
        $date = Carbon::parse($item->snippet->publishedAt);

        return new Video([
            'type' => 'video',
            'source' => 'youtube',
            'path' => "https://www.youtube.com/watch?v={$video_id}",
            'title' => $item->snippet->title,
            'video_id' => $video_id,
            'date' => $date->format('Y-m-d H:i:s'),
            'human_date' => $date->diffForHumans(),
            'description' => $item->snippet->description,
            'channel_url' => 'http://www.youtube.com/channel/' . $item->snippet->channelId,
            'channel_title' => $item->snippet->channelTitle,
            'is_imported' => false,
            'duration' => $time->getTimestamp(),
            'duration_formatted' => $time->format('i:s'),
            'thumbnail' => (isset($item->snippet->thumbnails->medium->url) ? $item->snippet->thumbnails->medium->url : (isset($item->snippet->thumbnails->high->url) ? $item->snippet->thumbnails->high->url : $item->snippet->thumbnails->low->url)),
            'views' => (isset($item->statistics) && isset($item->statistics->viewCount) ? $item->statistics->viewCount : 0),
            'state' => ''
        ]);
    }


}
