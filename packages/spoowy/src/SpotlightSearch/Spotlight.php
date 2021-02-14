<?php namespace Spoowy\SpotlightSearch;

use App\EmailHtml5Form;
use App\Image;
use App\Project;
use App\User;
use App\Video;
use App\Video as VideoFile;
use Elasticsearch\ClientBuilder;


/**
 * Class Spotlight
 * @package Spoowy\SpotlightSearch
 */
class Spotlight
{
    /**
     * Elasticsearch client
     * @var \Elasticsearch\Client
     */
    protected $elastic;

    public function __construct()
    {
        $this->elastic = ClientBuilder::create()->build(); //TODO: build client with params from config
    }

    /**
     * Elasticsearch query
     *
     * @param $query
     * @return array
     */
    public function search($query)
    {
        $params = [
            'index' =>  'spotlight',
            'body'  => [
                'query' => [
                    'multi_match' => [ // Match multi fields
                        'query' => $query,
                        'type' => 'cross_fields',
                        'fields' => [

                            /**
                             * ------------------------------------------------------------
                             * Project fields
                             * ------------------------------------------------------------
                             */

                            'project_title',

                            /*
                             * ------------------------------------------------------------
                             * User fields
                             * ------------------------------------------------------------
                             */

                            'first_name',
                            'last_name',
                            'email',
                            'phone',

                            /**
                             * ------------------------------------------------------------
                             * Images fields
                             * ------------------------------------------------------------
                             */
                            'title',
                        ]
                    ]
                ]
            ]
        ];

        $results = $this->elastic->search($params);
        $results = $this->buildCollection($results['hits']['hits']);

        return $results;
    }

    /**
     * Elasticsearch suggest
     *
     * @param $query
     * @return array
     */
    public function suggestion($query)
    {
        $params = [
            'index' => 'spotlight',
            'body'  => [
                'suggest' => [
                    'text' => $query,
//                    'term' => [
//                        'field' => 'body'
//                    ]
                ]
            ]
        ];

        $results = $this->elastic->suggest($params);

        return $results;
    }

    /**
     * Build Collection from search results
     *
     * @param  array $hits
     * @return array
     */
    private function buildCollection($hits)
    {
        $results = [];

        /**
         * Create Models from elasticsearch data
         */
        foreach($hits as $hit)
        {
            switch($hit['_type']) {
                case 'video_file':
                    $video_file = new VideoFile();
                    $video_file = $video_file->newFromBuilder($hit['_source'], true); // Create and sync with original
                    $results['video_files'][] = $video_file;
                    break;
                case 'video':
                    $video = new Video();
                    $video = $video->newFromBuilder($hit['_source']);
                    $results['video'][] = $video;
                    break;
                case 'project':
                    $project = new Project();
                    $project = $project->newFromBuilder($hit['_source']);
                    $results['projects'][] = $project;
                    break;
                case 'email_html5_form':
                    $email_html5_form = new EmailHtml5Form();
                    $email_html5_form = $email_html5_form->newFromBuilder($hit['_source'], true);
                    $results['email_html5_form'][] = $email_html5_form;
                    break;
                case 'image':
                    $image = new Image();
                    $image = $image->newFromBuilder($hit['_source'], true);
                    $results['image'][] = $image;
                    break;
                case 'user':
                    $user = new User();
                    /**
                     * @fix: newFromBuilder connection error
                     */
                    $user->newInstance($hit['_source'], true);
                    $user->setRawAttributes($hit['_source'], true);
                    $results['user'][] = $user;
                    break;
            }
        }

        /**
         * TODO: Reverse collection?
         */
        return $results;
    }
}