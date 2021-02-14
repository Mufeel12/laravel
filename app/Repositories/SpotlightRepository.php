<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 13.11.2015
 * Time: 0:09.
 */
namespace App\Repositories;

use Spatie\SearchIndex\SearchIndexFacade as SearchIndex;

/**
 * Class SpotlightRepository.
 */
class SpotlightRepository
{
    /**
     * Perform basic search.
     *
     * @param string $query
     * @param array  $types
     *
     * @return mixed
     */
    public function search($query = '', $types = [])
    {
        $query = [
            'index' => $this->getIndexName(),
            'type' => empty($types) ? $this->getSearchableTypes() : $types,
            'body' => [
                'query' => [ // TODO: not sure about this query, maybe need build better
                    'multi_match' => [ // Match multi fields
                        'query' => $query,
                        'type' => 'cross_fields',
                        'fields' => [

                            /*
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

                            /*
                             * ------------------------------------------------------------
                             * Images fields
                             * ------------------------------------------------------------
                             */
                            'title',
                        ],
                    ],
                ],
            ],
        ];

        $results = SearchIndex::getResults($query);

        return $results;
    }

    /**
     * Default searchable types.
     *
     * @return string
     */
    private function getSearchableTypes()
    {
        return config('searchindex.elasticsearch.defaultSearchableTypes');
    }

    private function getIndexName()
    {
        return config('searchindex.elasticsearch.defaultIndexName');
    }
}
