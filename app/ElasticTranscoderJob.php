<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ElasticTranscoderJob extends Model
{
    protected $table = 'elastic_transcoder_jobs';

    /**
     * Returns the corresponding video
     *
     * @return mixed
     */
    public function video()
    {
        // What is the best way to get the video from a elastic transcoder job?
        // Get name of file
        $filename = pathinfo($this->outputKey, PATHINFO_FILENAME); // returns e.g. 'videoId_240'

        // Get video id
        $parts = explode('_', $filename);
        if (isset($parts[1]))
            $videoId = array_shift($parts);
        else
            $videoId = $parts[0];

        return Video::where('video_id', $videoId)->first();
    }

    /**
     * Updates on Progress, success if done
     */
    public function progressUpdate()
    {
        // Are all jobs through or is one still waiting?
        $unfinishedChildren = $this->getOldUnfinishedTranscodingChildren();

        $stillInProgress = false;
        if (count($unfinishedChildren)) {
            $unfinishedChildren->each(function ($job) use (&$stillInProgress) {
                if (\Storage::exists($job->outputKey)) {
                    $job->outputStatus = 'Complete';
                    $job->state = 'COMPLETE';
                    $job->save();
                } else
                    $stillInProgress = true;
            });
        }

        if (!$stillInProgress)
            // Can we check whether their files exist? but only after they've been in for too long, whatever too long means
            // Update the transcoding job fire done
            VideoProcessingEvent::fire('transcode', $this->video(), 'success');
    }

    /**
     * Get old unfinished jobs
     *
     * @return mixed
     */
    public function getOldUnfinishedTranscodingChildren()
    {
        // return all elastic transcoding jobs that belong to the video
        $video = $this->video();
        $originalFile = $video->originalFile;
        if (count($originalFile)) {
            // Get filename of original file
            $pathInfo = pathinfo($originalFile->path);
            $inputKey = $pathInfo['basename'];

            return self::where('input_key', $inputKey)
                ->where('state', '!=', 'COMPLETE')
                ->get();
        }
    }
}
