<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 11.10.2015
 * Time: 9:13
 */

namespace App\Support\Adapters;

use Illuminate\Support\Collection;

/**
 * Class ImportVideo
 * @package App\Support\Adapters
 */
class LookupVideo
{

    /**
     * API handlers
     * @var array
     */
    protected $handlers = [];

    /**
     * @param array $handlers
     */
    public function __construct($handlers = [])
    {

        if (empty($handlers)) {
            $handlers = $this->makeDefaultHandlers();
        }

        $this->handlers = $handlers;
    }

    /**
     * Search video
     *
     * @param   string $query
     * @param   array $params
     * @return  array
     */
    public function search($query, $params)
    {
        $results = collect([]);

        /**
         * Check our query
         * If query is url, try search Video handler for this query and lookup just for that video source
         */
        if ($this->isUrl($query)) {

            foreach ($this->handlers as $handler) {

                $queryType = $handler->parseQuery($query);
                if ($queryType['type'] !== 'string') {

                    $results = $handler->search($query, $params);
                    return $results;
                }

            }
        }

        $sources = $params['sources'];

        foreach ($sources as $source) {
            // sometimes vimeo request times out
            try {
                if (isset($this->handlers[$source])) {
                    /** @var SearchVideoInterface $handler */
                    $handler = $this->handlers[$source];
                    $results[$handler->getName()] = $handler->search($query, $params);
                }
            } catch (\Exception $e) {
                return false;
            }
        }

        $results = $this->mergeResults($results);

        return $results;

    }

    /**
     * Merge search results
     *
     * Merge search results and sort them by views count
     *
     * @param  Collection $data
     * @return Collection
     */
    private function mergeResults($data)
    {

        $results = collect([]);
        $merged_data = collect([]);

        foreach ($data as $key => $item) {
            if ($item['data'] instanceof Collection) {
                $merged_data = $merged_data->merge($item['data']);
                $results["{$key}_next_page"] = $item['next_page'];
            }
        }

        $results['data'] = $merged_data;#$merged_data->shuffle();

        return $results;
    }

    /**
     * Check string is url
     *
     * @param $string
     * @return bool
     */
    private function isUrl($string)
    {
        $regex = '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*/';

        preg_match($regex, $string, $matches);

        return empty($matches) ? false : true;
    }

    /**
     * Default video api handlers
     * @return array
     */
    private function makeDefaultHandlers()
    {
        return [
            'vimeo' => new VimeoAdapter(),
            'youtube' => new YoutubeAdapter()
        ];
    }
}