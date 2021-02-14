<?php

namespace App\Jobs;

use App\User;
use App\Video;
use App\VideoProcessingEvent;
use App\VideoSubtitle;
use Aws\S3\S3Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Google\Cloud\Speech\V1\SpeechClient;
use Google\Cloud\Speech\V1\RecognitionAudio;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Google\Cloud\Storage\StorageClient;

class ExtractAudioFromVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $video;
    protected $request;
    protected $job_id;
    protected $subtitle;

    public $tries = 1;
    public $timeout = 7200;

    public function __construct($video, $request, $job_id, $subtitle)
    {
        $this->video = $video;
        $this->request = $request;
        $this->job_id = $job_id;
        $this->subtitle = $subtitle;

    }


    public function handle()
    {
        $subtitle = $this->subtitle;
        $subtitle->update(['sub_status' => 'in_progress']);
        Cache::forever($this->job_id, 'generating');
        putenv("GOOGLE_APPLICATION_CREDENTIALS=" . base_path('video-encoding-and-dam-594670ca2d01.json'));
        $languageCode = [
            'en' => 'en-US',
            'es' => 'es-ES',
            'fr' => 'fr-FR',
            'zh' => 'zh (cmn-Hans-CN)',
            'it' => 'it-IT',
            'vi' => 'vi-VN',
            'pt' => 'pt-BR',
            'ar' => 'ar-EG',
            'de' => 'de-DE',
            'hi' => 'hi-IN',
            'jv' => 'ja-JP',
            'ko' => 'ko-KR',
            'ro' => 'ro-RO',
            'pl' => 'pl-PL',
            'ga' => 'en-US',
            'nl' => 'nl-NL',
            'he' => 'iw-IL',
            'jv' => 'jv-ID',
            'id' => 'id-ID',
            'tl' => 'fil-PH',
            'cs' => 'cs-CZ',
            'th' => 'th-TH',
            'ur' => 'ur-PK',
            'ms' => 'ms-MY',
            'fa' => 'fa-IR',
            'fi' => 'fi-FI',
            'tr' => 'tr-TR',
            'da' => 'da-DK',
            'sv' => 'sv-SE',
        ];

        $video = $this->video;
        $stored_name = strtolower(Str::random(32)) . '.wav';
//        $output = $audio_link = storage_path('data/audio/' . $stored_name);


        $storage = new StorageClient();
        $bucket = $storage->bucket('adilo_subtitles');
        $bucket->upload(file_get_contents($video->audio_url), [
            'name' => $stored_name
        ]);


        $command = 'ffprobe -i ' . $video->audio_url . ' ' . '-v quiet -print_format json -show_format -show_streams -hide_banner';

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


        $audioFile = $video->audio_url;
        $model = 'video';

        // change these variables if necessary
        $encoding = AudioEncoding::LINEAR16;
        $sampleRateHertz = $properties['streams'][0]['sample_rate'];
        $lanCode = $languageCode[$this->request['language']];

        // get contents of a file into a string
        $content = file_get_contents($audioFile);

        // set string as audio content
        $audio = (new RecognitionAudio())
            ->setContent($content);

        // set config


        $config = new RecognitionConfig([
//            'keyFilePath' => base_path('video-encoding-and-dam-594670ca2d01.json'),
            'encoding' => AudioEncoding::LINEAR16,
            'sample_rate_hertz' => $properties['streams'][0]['sample_rate'],
            'language_code' => $lanCode,
            'enable_word_time_offsets' => true,
            'model' => 'video'
        ]);
        $audio = (new RecognitionAudio())
            ->setUri('gs://adilo_subtitles/' . $stored_name);
        // create the speech client
        $client = new SpeechClient();

// create the asyncronous recognize operation
        $operation = $client->longRunningRecognize($config, $audio);
        $operation->pollUntilComplete();


        $string = "";
        $c = 0;
        $all_captions[$c] = [];
        $rand = rand(15, 20);
        if ($operation->operationSucceeded()) {
            $response = $operation->getResult();
            // print results
            foreach ($response->getResults() as $i => $result) {

                $alternatives = $result->getAlternatives();
                $mostLikely = $alternatives[0];
                $transcript = $mostLikely->getTranscript();
                $confidence = $mostLikely->getConfidence();

//                dd($mostLikely->getWords());

                dump('....');
                dump('Transcript: ' . PHP_EOL, $transcript);
                dump('Confidence: ' . PHP_EOL, $confidence);

                $words = $mostLikely->getWords();

                $total_words = count($words);

                foreach ($mostLikely->getWords() as $index => $wordInfo) {
                    $startTime = trim($wordInfo->getStartTime()->serializeToJsonString(), '"');
                    $startTime = trim($startTime, 's');
//                    $arr = explode('.', $startTime);
//                    $start = gmdate("H:i:s", $arr[0]) . '.' . (isset($arr[1]) ? $arr[1] : '000');

                    $endTime = trim($wordInfo->getEndTime()->serializeToJsonString(), '"');
                    $endTime = trim($endTime, 's');
//                    $arr = explode('.', $endTime);
//                    $end = gmdate("H:i:s", $arr[0]) . '.' . (isset($arr[1]) ? $arr[1] : '000');


                    if (count($all_captions[$c]) == $rand) {
                        $c++;
                        $rand = rand(15, 20);
                    }
                    $all_captions[$c][] = [
                        'start' => $startTime,
                        'end' => $endTime,
                        'word' => $wordInfo->getWord()
                    ];


                }
            }
        } else {
            dump($operation->getError());
        }


//        dd($all_captions);

        if (!empty($all_captions)) {

            foreach ($all_captions as $index => $caption) {
                if ($index == 0) {
                    $string .= 'WEBVTT FILE
';

                }


                $sub_str = ' ';

                $last_index = count($caption) - 1;
                $start = explode('.', $caption[0]['start']);
                $end = explode('.', $caption[$last_index]['end']);

                foreach ($caption as $i => $cap) {
                    $sub_str .= $cap['word'] . ' ';
                }


                $string .= '
' . ($index + 1) . '
' . gmdate("H:i:s", $start[0]).'.'. (isset($start[1]) ? $start[1] : '000') . ' --> ' . gmdate("H:i:s", $end[0]).'.' . (isset($end[1]) ? $end[1] : '000') . '
' . trim($sub_str, ' ') . '
';



            }

        }

        $new_file_name = $subtitle->stored_name;
        file_put_contents(storage_path('data/subtitles/' . $new_file_name), $string);
        chmod(storage_path('data/subtitles/' . $new_file_name), 0777);
        
        $video = VideoSubtitle::where('id', $subtitle->id)->with('video')->first();
        $user_row = User::find($video->video->owner);
        $user = User::getUserDetails($user_row);
        $owner_folder = generate_owner_folder_name($user->owner->email);
        $file_key = "{$owner_folder}/{$video->video->video_id}/subtitles/{$new_file_name}";


        $endpoint = config('aws.endpoint');
        $bucket = config('aws.bucket');

        $s3 = S3Client::factory(array(
            'endpoint' => $endpoint,
            'region' => config('aws.region'),
            'version' => config('aws.version'),
            'credentials' => config('aws.credentials')
        ));

        try {
            $s3->putObject(array(
                'Bucket' => $bucket,
                'Key' => $file_key,
                'Body' => file_get_contents(storage_path('data/subtitles/' . $new_file_name)),
//                'ContentLength' => $file_size,
                'ACL' => 'public-read'
            ));

            $file_path = $s3->getObjectUrl($bucket, $file_key);


            $video->update(['url' => $file_path, 'status' => 1]);
        } catch (\Exception $exception) {
            $video->update(['url' => $file_path, 'status' => 2]);
        }

        $bucket = $storage->bucket('adilo_subtitles');
        $object = $bucket->object($stored_name);
        $object->delete();

        $client->close();

        Cache::forever($this->job_id, 'generated');
        $video->update(['sub_status' => 'completed']);

    }

    public function failed()
    {
        Cache::forever($this->job_id, 'failed');
        $this->subtitle->update(['sub_status' => 'failed']);
    }

}
