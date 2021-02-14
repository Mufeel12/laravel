<?php namespace App\Observers;
use App\User;
use App\VideoFileStorage;


/**
 * Class VideoFileStorage
 * @package App\Observers
 */
class VideoFileStorageObserver
{
    public function __construct()
    {

    }

    /**
     * Created model
     *
     * @param \App\VideoFileStorage $storage
     */
    public function created(VideoFileStorage $storage)
    {
        $userId = $storage->user_id;
        $freeSpace = User::getFreeSpace($userId);

        if ($freeSpace >= env('MIN_STORAGE_SPACE_FOR_NOTIFICATION')) {
            /*Notification::sendNotification([
                'title' => 'Running out of free space',
                'text' => 'Only ' . format_bytes($freeSpace) . ' free space available on your account. Please upgrade to a higher plan.',
                'icon' => 'default',
                'type' => 'info',
                'link' => route('upgradePlan')
            ], $userId);*/
            // Todo: send notification
        }
    }

    /**
     * Updated model
     *
     * @param VideoFileStorage $storage
     */
    public function updated(VideoFileStorage $storage)
    {

    }

    /**
     * Deleted model
     *
     * @param VideoFileStorage $storage
     */
    public function deleted(VideoFileStorage $storage)
    {

    }

}