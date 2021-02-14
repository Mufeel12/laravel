<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 16.07.2015
 * Time: 20:19
 */

namespace App\Observers;

use App\VideoProcessingEvent;

/**
 * Class VideoProcessingEventObserver
 *
 * Observer for Video model
 * @package app\Observers
 */
class VideoProcessingEventObserver
{
    /**
     * Model created event
     *
     * @param VideoProcessingEvent $event
     */
    public function created(VideoProcessingEvent $event)
    {
        
    }

    /**
     * Model updated event
     *
     * @param VideoProcessingEvent $event
     */
    public function updated(VideoProcessingEvent $event)
    {
        
    }

    /**
     * Model deleted event
     *
     * @param VideoProcessingEvent $event
     */
    public function deleted(VideoProcessingEvent $event)
    {
        
    }
}