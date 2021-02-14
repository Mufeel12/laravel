<?php

namespace App;

use App\Notifications\ProcessingCompleted;
use Illuminate\Database\Eloquent\Model;

class VideoProcessingEvent extends Model
{
    protected $fillable = ['video_id', 'event', 'status'];

    /**
     * Odered list of events to take place
     *
     * @var array
     */
    protected $eventsQueue = [
        'upload',
        'rename',
        'transcode',
        'deleteoriginalfile',
        'thumbnail',
        'scrumb',
        'unlock',
        'notify'
    ];

    /**
     * A list of events that only apply to amazon uploaded videos
     *
     * @var array
     */
    protected $eventsExclusivelyForS3 = [
        'rename',
        'transcode',
        'deleteoriginalfile',
        'path'
    ];

    /**
     * Fires an event
     *
     * @param $event
     * @param Video $video
     * @param string $status
     * @return bool
     */
    public static function fire($event, Video $video, $status = 'progress')
    {
        $event = strtolower(trim($event));

        /*$eventEntry = self::where('video_id', $video->id)
            ->where('event', $event)
            ->orderBy('id', 'DESC')
            ->first();

        if (count($eventEntry)) {
            // Update entry if exists
            $eventEntry->status = strtolower(trim($status));
            $eventEntry->save();
        } else {
            // Create new
            $eventEntry = self::create([
                'video_id' => $video->id,
                'event' => strval($event),
                'status' => strval($status)
            ]);
        }*/

        $alreadyExistingEventEntry = self::where('video_id', $video->id)
            ->where('event', strval($event))
            ->where('status', 'success')
            ->get();

        $tryFromLatest = true;
        // Get last event that failed / was progressing move on from there
        if (count($alreadyExistingEventEntry) && $tryFromLatest) {
            $eventEntry = self::where('video_id', $video->id)
                ->where('status', '!=', 'error')
                ->orWhere('status', '!=', 'progressing')
                ->orderBy('id', 'DESC')
                ->first();

            return $eventEntry->next();
        }
        // instead create a new one
        $eventEntry = self::create([
            'video_id' => $video->id,
            'event' => strval($event),
            'status' => strval($status)
        ]);


        // If the current event was a success, continue with the next one
        if ($eventEntry->status == 'success')
            return $eventEntry->next();

        // Retry if it was an error
        if ($eventEntry->status == 'error') {

            #return $eventEntry->next();
            // Todo: Check if last three attempts have been successful or not

            $previousWithErrors = self::where('video_id', $video->id)
                ->where('event', $eventEntry->event)
                ->where('status', 'error')
                ->count();

            // Decide to try again or to move on with the next task
            if ($previousWithErrors < 3) {
                return self::fire($eventEntry->event, $video);
            }
            // Log that this was skipped
            return $eventEntry->next();
        }

        return $eventEntry;
    }

    /**
     * Trims and strtolowers event string
     *
     * @param $value
     * @return string
     */
    public function getEventAttribute($value)
    {
        return trim(strtolower($value));
    }

    /**
     * Returns corresponding video
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function video()
    {
        return $this->hasOne('\App\Video', 'id', 'video_id');
    }

    /**
     * Executes next event
     *
     * @return bool
     */
    public function next()
    {
        $nextEvent = $this->getNextEvent();

        if (!$nextEvent)
            return false;

        // Store to db that the next one is already in progress
        $event = self::fire($nextEvent, $this->video);

        try {
            switch ($nextEvent) {
                case 'upload':
                    // Upload usually is already done
                    self::fire('upload', $this->video, 'success');
                    break;
                case 'rename':
                    $this->video->renameOriginalS3File();
                    break;
                case 'transcode':
                    $this->video->transcode();
                    break;
                case 'deleteoriginalfile':
                    $this->video->deleteOriginalFile();
                    break;
                case 'thumbnail':
                    $this->video->generateDefaultThumbnail();
                    break;
                case 'scrumb':
                    $this->video->generateScrumb();
                    break;
                case 'unlock':
                    $this->video->unlock();
                    break;
                case 'notify':
                    // Todo: send laravel notification, processingComplete
                    $user = User::find($this->video->owner);
                    if (count($user) == 1)
                        $user->notify(new ProcessingCompleted($this->video));
                    break;
            }
        } catch (\Exception $e) {
            \Log::error($e->getMessage(), (array)$e);
            // Fire error
            self::fire($event->event, $this->video, 'error');
        }
    }

    /**
     * Returns next event in queue
     */
    public function getNextEvent()
    {
        // Remove events that are only for s3 if this is not a s3 uploaded video
        $eventsQueue = array_values($this->eventsQueue);

        if ($this->video->imported) {
            $eventsQueue = array_values(array_diff($eventsQueue, $this->eventsExclusivelyForS3));
        }

        $arrayPosition = array_search($this->event, $eventsQueue);
        return (isset($eventsQueue[$arrayPosition + 1]) ? $eventsQueue[$arrayPosition + 1] : false);
    }
}
