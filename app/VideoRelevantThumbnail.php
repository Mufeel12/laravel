<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoRelevantThumbnail extends Model
{
    protected $fillable = ['*'];

    public static function duplicateRelevantThumbnails($oldVideoId, $newVideoId)
    {
        // Get thumbnails
        $relevantThumbnails = self::where('key', $oldVideoId)->get();

        if (count($relevantThumbnails) > 0) {
            // Loop through and duplicate
            foreach ($relevantThumbnails as $relevantThumbnail) {
                $duplicateThumbnail = new self();
                $duplicateThumbnail->key = $newVideoId;
                $duplicateThumbnail->url = $relevantThumbnail->url;
                $duplicateThumbnail->save();
            }
        }
    }
}
