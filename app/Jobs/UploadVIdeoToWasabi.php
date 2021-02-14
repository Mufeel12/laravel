<?php

namespace App\Jobs;

use App\User;
use App\Video;
use App\VideoProcessingEvent;
use Aws\S3\S3Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use File;

class UploadVIdeoToWasabi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $video;
    protected $file_path;
    protected $drm_protection;
    protected $videoType;
    public $tries = 1;

    public function __construct(Video $video, $file_path, $drm_protection, $videoType = null)
    {
        $this->video = $video;
        $this->file_path = $file_path;
        $this->drm_protection = $drm_protection;
        $this->videoType = $videoType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {


        if (!file_exists($this->file_path)) {
            $this->video->update(['wasabi_status' => 3]); // 3 indicates file not found on server
        }

        $user_row = User::find($this->video->owner);
        // $user = User::getUserDetails($user_row);

        $owner_folder = generate_owner_folder_name($user_row->email);

        $file_ext = pathinfo($this->file_path, PATHINFO_EXTENSION);

        $file_key = "{$owner_folder}/{$this->video->video_id}/{$this->video->video_id}.{$file_ext}";

        $put_object = Video::uploadFileToBucket($file_key, $this->file_path, $this->video->transcoding_size_source);

        if (isset($put_object['status']) && !$put_object['status']) {
            throw new \Exception('Upload video to wasabi is failed!');
        }

        $file_path = $put_object['file_path'] ?? '';

        $this->video->update([
            'path' => $file_path,
            'wasabi_status' => 1   // 1 Indicates successfull upload.
        ]);

        if (!$this->videoType) {

            if ($this->video->wasabi_status == 1) {
                $is_audio = false;

                $command = 'ffprobe -i "' . $this->file_path . '" ' . '-v quiet -print_format json -show_format -show_streams -hide_banner';

                $process = new Process($command);
                $process->setTimeout(config('env.QUEUE_TIMEOUT'));
                $process->setIdleTimeout(config('env.QUEUE_TIMEOUT'));
                $process->start();

                $process->wait();

                if (!$process->isSuccessful()) {
                    throw new ProcessFailedException($process);
                }

                $output = $process->getOutput();
                $properties = json_decode($output, true);

                foreach ($properties['streams'] as $stream) {
                    if ($stream['codec_type'] == 'audio') {
                        $is_audio = true;
                    }
                }

                if (!$this->videoType && $is_audio) {

                    $rand = strtolower(Str::random(32));
                    $stored_name = $rand . '.wav';
                    $output = storage_path('data/audio/' . $stored_name);
                    $ext = pathinfo($this->file_path, PATHINFO_EXTENSION);
                    rename($this->file_path, storage_path('uploads/' . $rand . '.' . $ext));

                    $command = "ffmpeg -i " . storage_path('uploads/' . $rand . '.' . $ext) . " -vn -acodec pcm_s16le -ar 44100 -ac 1 " . $output;
                    $process = new Process($command);
                    $process->setTimeout(config('env.QUEUE_TIMEOUT'));
                    $process->setIdleTimeout(config('env.QUEUE_TIMEOUT'));
                    $process->start();
                    $process->wait();

                    if (!$process->isSuccessful()) {
                        dump($process->isSuccessful());
//                        throw new \Exception('Audio is failed!');
                        throw new ProcessFailedException($process);
                    } else {

                        $file_key = "{$owner_folder}/{$this->video->video_id}/audio/{$stored_name}";
                        $size = File::size($output);
                        $put_object = Video::uploadFileToBucket($file_key, $output, $size);

                        if (isset($put_object['status']) && !$put_object['status']) {
                            throw new \Exception('Upload video to wasabi is failed!');
                        }

                        $audio_file_path = $put_object['file_path'] ?? '';

                        $this->video->update([
                            'audio_url' => $audio_file_path
                        ]);
                        if (file_exists($output)) {
                            unlink($output);
                        }
                    }
                    if (file_exists($this->file_path)) {
                        unlink(storage_path('uploads/' . $rand . '.' . $ext));
                    }
                    $renamedName = storage_path('uploads/' . $rand . '.' . $ext);
                    if (file_exists($renamedName)) {
                        unlink($renamedName);
                    }
                }
            }


        }


        $data = array(
            'action' => 'add',
            'input' => array(
                'url' => trim($file_path),
                'video_id' => $this->video->video_id,
                'account_folder' => $owner_folder,
                'drm' => $this->drm_protection,
                'callback' => config('env.CALLBACK_VIDEO')
            ),
            'output' => array(
                'output' => 'wasabi',
                "gif" => [
                    "timestamp" => [5]
                ],
            ),
        );
        dump($data);
        \Log::info(json_encode($data) . ' --- Upload-video-ToWasabi ');

        doApiRequest($data);

        if (file_exists($this->file_path)) {
            unlink($this->file_path);
        }
    }

    public function failed()
    {
        $this->video->update(['wasabi_status' => 2]); // 2 indicates upload to wasabi failed.
    }

}
