<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 16.07.2015
 * Time: 20:19
 */

namespace App\Observers;

use App\ElasticTranscoderJob;
use App\VideoQualityFile;

/**
 * Class ElasticTranscoderJob
 *
 * Observer for ElasticTranscoderJob model
 * @package app\Observers
 */
class ElasticTranscoderJobObserver
{
    /**
     * Model created event
     *
     * @param ElasticTranscoderJob $entry
     */
    public function created(ElasticTranscoderJob $entry)
    {

    }

    /**
     * Model updated event
     *
     * @param ElasticTranscoderJob $entry
     */
    public function updated(ElasticTranscoderJob $entry)
    {

    }

    /**
     * Model deleted event
     *
     * @param ElasticTranscoderJob $entry
     */
    public function deleted(ElasticTranscoderJob $entry)
    {

    }
}