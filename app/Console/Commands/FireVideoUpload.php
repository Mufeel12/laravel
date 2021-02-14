<?php

namespace App\Console\Commands;

use App\VideoProcessingEvent;
use App\Support\Models\Video;
use Illuminate\Console\Command;

class FireVideoUpload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fireVideoUpload {videoId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fires video upload success';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // TODO This shit breaks, firing way too often
        $videoId = $this->argument('videoId');
        $video = \App\Video::find($videoId);
        if (count($video)) {
            \App\VideoProcessingEvent::fire('upload', $video, 'success');
        }
    }
}
