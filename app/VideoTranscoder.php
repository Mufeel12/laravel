<?php

namespace App;

use Aws\Laravel\AwsFacade;
use Illuminate\Database\Eloquent\Model;

class VideoTranscoder extends Model
{
    protected $table = 'elastic_transcoder_jobs';

    /**
     * Transcodes a video via ElasticTranscoder
     *
     * @param \App\Video $video
     * @return array
     */
    public static function transcode(Video $video)
    {
        // Base for new filename
        $base = $video->video_id . '.mp4';
        $videoInfo = $video->info;

        // Get ElasticTranscoder instance
        $transcoder = AwsFacade::createClient('ElasticTranscoder');

        // Transcode standard video quality first
        $preset = [
            'id' => '1351620000001-000030',
            'key' => $video->video_id . '.mp4',
            'quality' => 480
        ];
        $result[] = self::createJob($video->video_id, $transcoder, $video->filename, $preset);

        $presets = [];
        /*if (isset($videoInfo->streams[0]->width)) {
            // Video is of quality 1080hd or higher
            $width = $videoInfo->streams[0]->width;
            if ($width >= 1920) {
                $presets[] = [
                    'id' => '1351620000000-000001',
                    'key' => $video->video_id . '_1080hd.mp4',
                    'quality' => 1080
                ];
            }
            // Video is of quality 720hd or higher
            if ($width >= 1280) {
                $presets[] = [
                    'id' => '1351620000001-000010',
                    'key' => $video->video_id . '_720hd.mp4',
                    'quality' => 720
                ];
            }
            // Video is of quality 720hd or higher
            if ($width >= 480) {
                $presets[] = [
                    'id' => '1351620000001-000010',
                    'key' => $video->video_id . '_360hd.mp4',
                    'quality' => 360
                ];
            }
        }*/

        // Todo: this needs to be set individually per video
        // Video is of quality 1080hd or higher
        $presets[] = [
            'id' => '1351620000000-000001',
            'key' => $video->video_id . '_1080hd.mp4',
            'quality' => 1080
        ];
        // Video is of quality 720hd or higher
        $presets[] = [
            'id' => '1351620000001-000010',
            'key' => $video->video_id . '_720hd.mp4',
            'quality' => 720
        ];

        // Video is of quality 720hd or higher
        $presets[] = [
            'id' => '1351620000001-000010',
            'key' => $video->video_id . '_360hd.mp4',
            'quality' => 360
        ];

        // Video for mobile quality
        $presets[] = [
            'id' => '1351620000001-000061',
            'key' => $video->video_id . '_240p.mp4',
            'quality' => 240
        ];

        // let's transcode the rest
        foreach ($presets as $preset) {
            try {
                $result[] = self::createJob($video->video_id, $transcoder, $video->filename, $preset);
            } catch (\Exception $e) {}
        }

        // Fire event
        VideoProcessingEvent::fire('transcode', $video);

        return $result;
    }

    /**
     * Fires job to AWS ElasticTranscoder
     *
     * @param $videoId
     * @param $transcoder
     * @param $key
     * @param $preset
     * @return mixed
     */
    public static function createJob($videoId, $transcoder, $key, $preset)
    {
        \Log::error('transcoding key: '. $key);
        $job = $transcoder->createJob([
            'PipelineId' => env('AWS_ELASTIC_TRANSCODER_PIPELINE_ID'),
            'Input' => array(
                'Key' => $key
            ),
            'Output' => array(
                'Key' => 'production/' . $preset['key'],
                'PresetId' => $preset['id']
            )
        ]);

        // Save quality file into database
        $qualityFile = new VideoQualityFile();
        $qualityFile->video_id = $videoId;
        $qualityFile->format = $preset['quality'];
        $qualityFile->path = env('VIDEO_STORAGE') . 'production/' . urlencode($preset['key']);
        $qualityFile->status = 'Submitted'; #Progressing #Error #Complete
        $qualityFile->save();

        return $job;
    }

    /**
     * Returns 'Complete' if completed, otherwise investigates status of job.
     *
     * @param Video $video
     * @return string
     * @internal param $value
     */
    public static function state(Video $video)
    {
        $videoQualityFiles = VideoQualityFile::where('video_id', $video->video_id)->get();

        $state = 'Progress';
        foreach ($videoQualityFiles as $qualityFile) {
            if ($qualityFile->status != 'Submitted'
                || $qualityFile->status != 'Progressing'
                || $qualityFile->status != 'Progress'
                || $qualityFile->status != 'Pending'
            ) {
                $state = 'Complete';
            } else {
                // Retry transcoding
                #$video->transcode();
            }
        }
        return $state;
    }
}
