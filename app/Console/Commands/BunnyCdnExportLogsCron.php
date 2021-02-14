<?php

namespace App\Console\Commands;

use App\BunnyCDNBandwidthRecords;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BunnyCdnExportLogsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bunny-cdn-export:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $today = date('Y-m-d H:i:s');
        $exporting_day = date('m-d-y', strtotime("-1 days"));

        $last_exported_date = DB::table('exported_logs_activities')->latest('last_export')->first();
        if ($last_exported_date != null && $last_exported_date->last_export != null) {
            $last_exported_log_day = date('d', strtotime($last_exported_date->last_export));
            $today_day_number = date('d', strtotime($today));
            if ($today_day_number == $last_exported_log_day) {
                $exporting_day = date('m-d-y');
            }
        }

        $client = new \GuzzleHttp\Client();
        $request = new \GuzzleHttp\Psr7\Request('GET', "https://logging.bunnycdn.com/{$exporting_day}/91729.log?download=false&status=2xx,3xx", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'AccessKey' => '34d2ad60-ba8c-436a-9d2a-27e27af4996adc7a2754-e708-4dbd-9340-5c87a476bd3b',
        ]);


        try {
            $promise = $client->sendAsync($request)->then(function ($response) {
                $result = $response->getBody()->getContents();
                $status = (int)$response->getStatusCode();

                /* if the request has no problems */
                if ($status == 200) {
                    $file_names = [];
                    $data_count = 0;

                    /* regular expression to receive formatted data */
                    $regex = '/([A-Za-z]+)\|([0-9]{3})\|([0-9]+)\|([0-9]+)\|([0-9]+)\|([0-9\.]+)\|(.*)\|(http.*)\/([A-Za-z0-9_]{8}+)(\/|\.|\-)(.*)\|([A-Z]+)\|/m';
                    preg_match_all($regex, $result, $matches, PREG_SET_ORDER, 0);

                    foreach ($matches as $match) {
                        $status_code = $match[2];
                        $timestamp = $match[3];
                        $bytes_sent = $match[4];
                        $ip_address = $match[6];
                        $referer_url = $match[7];
                        $video_id = $match[9];
                        $file_name = $match[8] . '/' . $video_id . $match[10] . $match[11];
//                        $needle = $status_code . '-' . $timestamp . '-' . $bytes_sent . '-' . $ip_address . '-' . $referer_url . '-' . $file_name;
                        $needle = "{$status_code}-{$timestamp}-{$bytes_sent}-{$ip_address}-{$referer_url}-{$file_name}";
                        /* check if logs are not repeated */
                        if (!in_array($needle, $file_names)) {
                            $file_names[] = $needle;
                            $log_exist = BunnyCDNBandwidthRecords::where(['unique_log' => $needle])->first();

                            /* check if the log does not exist */
                            if (is_null($log_exist)) {

                                try {
//                                    BunnyCDNBandwidthRecords::create([
//                                        'video_id' => $video_id,
//                                        'unique_log' => $needle,
//                                        'file_name' => $file_name,
//                                        'timespan' => 0,
//                                        'timestamp' => $timestamp,
//                                        'bytes_sent' => $bytes_sent,
//                                        'remote_ip_address' => $ip_address,
//                                        'created_at' => now(),
//                                        'updated_at' => now(),
//                                    ]);

                                    $new_log = new BunnyCDNBandwidthRecords();
                                    $new_log->video_id = $video_id;
                                    $new_log->unique_log = $needle;
                                    $new_log->file_name = $file_name;
                                    $new_log->timespan = 0;
                                    $new_log->timestamp = $timestamp;
                                    $new_log->bytes_sent = $bytes_sent;
                                    $new_log->remote_ip_address = $ip_address;
                                    $new_log->created_at = now();
                                    $new_log->updated_at = now();
                                    $new_log->save();

                                    $data_count += 1;

                                } catch (\Exception $exception){
                                    Log::info("Log not saved ". $exception->getMessage());
                                }
                            }
                        }
                    }

                    if ($data_count > 0) {
                        Log::info("New logs exported successfully. Number of exported logs - " . $data_count);
                    } else {
                        Log::info("No new logs for export");
                    }

                    /* Keep last export time */
                    DB::table('exported_logs_activities')->insert(['last_export' => date('Y-m-d H:i:s')]);
                }
            });

            $promise->wait();
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());
        }
    }
}