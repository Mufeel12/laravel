<?php

namespace App\Http\Controllers;

use App\ElasticTranscoderJob;
use App\VideoProcessingEvent;
use App\VideoQualityFile;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use SplFileObject;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Aws\Sns\Exception\InvalidSnsMessageException;

class AmazonSnsController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $message = Message::fromRawPostData();
        \Log::error('called notification validator');
        /*// Instantiate the Message and Validator
        $validator = new MessageValidator();

        // Validate the message and log errors if invalid.
        try {
            $validator->validate($message);
        } catch (InvalidSnsMessageException $e) {
            // Pretend we're not here if the message is invalid.
            http_response_code(404);
            error_log('SNS Message Validation Error: ' . $e->getMessage());
            die();
        }

        // Check the type of the message and handle the subscription.
        if ($message['Type'] === 'SubscriptionConfirmation') {
            // Confirm the subscription by sending a GET request to the SubscribeURL
            $client = new Client();
            $request = $client->get($message['SubscribeURL']);
            $request->getBody();
            file_get_contents($message['SubscribeURL']);
        }
        die();*/
        $message = (array)$message;
        $message = (object)array_shift($message);

        // Log the message
        $file = new SplFileObject(public_path() . '/../messages.log', 'a');
        $file->fwrite($message->Type . ': ' . $message->Message . "\n");

        $message = json_decode($message->Message);

        \Log::alert('the amazon sns was fired.');
        #\Log::alert($message->jobId);

        $ej = ElasticTranscoderJob::where('jobId', $message->jobId)->first();
        if (!count($ej))
            $ej = new ElasticTranscoderJob();
        $ej->jobId = $message->jobId;
        $ej->state = $message->state;
        $ej->version = $message->version;
        $ej->pipelineId = $message->pipelineId;
        $ej->input_key = $message->input->key;
        $ej->outputId = $message->outputs[0]->id;
        $ej->outputPresetId = $message->outputs[0]->presetId;
        $ej->outputKey = $message->outputs[0]->key;
        $ej->outputStatus = $message->outputs[0]->status;
        $ej->outputDuration = (isset($message->outputs[0]->duration) ? $message->outputs[0]->duration : 0);
        $ej->outputWidth = (isset($message->outputs[0]->width) ? $message->outputs[0]->width : 0);
        $ej->outputHeight = (isset($message->outputs[0]->height) ? $message->outputs[0]->height : 0);
        $ej->save();

        $ej->progressUpdate();

        /*// Update the original file
        $videoQualityFile = VideoQualityFile::where('path', env('VIDEO_STORAGE') . $message->outputs[0]->key)->first();
        $videoQualityFile->status = $message->outputs[0]->status;
        $videoQualityFile->save();*/
    }
}
