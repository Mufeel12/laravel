<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 14.11.2015
 * Time: 23:42
 */

namespace App\Formatters;

use App\ffmpegJobs;
use Illuminate\Support\Collection;

/**
 * Class C3ChartFormatter
 * @package App\Formatters
 */
class C3ChartFormatter
{

    /**
     * Group by key
     * @var string
     */
    protected $groupByKey = 'date';

    /**
     * Columns
     * @var array
     */
    protected $columns = [];

    /**
     * @var Collection
     */
    protected $data;

    /**
     * @param mixed $data
     */
    public function setData(Collection $data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getGroupByKey()
    {
        return $this->groupByKey;
    }

    /**
     * @param string $groupByKey
     */
    public function setGroupByKey($groupByKey)
    {
        $this->groupByKey = $groupByKey;
    }

    /**
     * @param array $columns
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * Format columns
     *
     * @return Collection
     */
    public function columns()
    {
        $formattedData = $this->getData();

        return collect(array_map(function($value) use ($formattedData) {
            return $formattedData->pluck($value)->prepend(str_plural($value));
        }, $this->columns));

    }

    public function formatData($isEngagement = false)
    {
        $this->data = $this->format($this->data, $isEngagement);
    }

    /**
     * Format collection
     *
     * Fill empty days, normalize data
     *
     * @param Collection $records
     * @param bool $isEngagement
     * @return array
     */
    private function format(Collection $records, $isEngagement = false)
    {
        // TODO: clean up engagement, organise better
        if ($isEngagement) {
            $records = $records->groupBy('time');

            $formattedRecords = $records->map(function($item) {
                $actions = $item->map(function($item) {

                    return [
                        'time' => $item->time,
                        'engagement' => $item->engagement,
                        'replays' => $item->replays,
                        'skipped' => $item->skipped
                    ];
                });

                return $actions->collapse();
            });

            return $formattedRecords->values();
        }

        $records = $records->groupBy($this->getGroupByKey());

        $formattedRecords = $records->map(function($item) {
            $actions = $item->map(function($item) {

                return [
                    'date' => $item->date,
                    $item->action => $item->count
                ];
            });

            return $actions->collapse();
        });

        return $formattedRecords->values();
    }

}