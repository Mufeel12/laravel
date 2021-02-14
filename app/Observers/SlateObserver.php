<?php namespace App\Observers;

use App\Slate;

/**
 * Class SlateObserver
 * @package App\Observers
 */
class SlateObserver
{
    public function __construct()
    {

    }

    /**
     * Created model
     *
     * @param Slate $slate
     */
    public function created(Slate $slate)
    {
        $slate->createThumbnail($slate);
    }

    /**
     * Updated model
     *
     * @param Slate $slate
     */
    public function updated(Slate $slate)
    {
        $slate->createThumbnail($slate);
    }

    /**
     * Deleted model
     *
     * @param Slate $slate
     */
    public function deleted(Slate $slate)
    {
        $slate->deleteThumbnail($slate);
    }

}