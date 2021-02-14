<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BunnyCDNBandwidthRecords extends Model
{
    protected $table = 'bunnycdn_bandwidth_records';

    protected $fillable = ['*'];

    public static function extractVideoIdFromUrl($url)
    {
        // $url = 'https://bigcomman.b-cdn.net/mmvs/0nPJ7qmq.mov-03.png"';
        $filename = pathinfo($url, PATHINFO_FILENAME);
        $fileparts = explode('.', $filename);
        return $fileparts[0];
    }

    public function video() {
        return $this->belongsTo('\App\Video', 'video_id', 'video_id');
    }
}
