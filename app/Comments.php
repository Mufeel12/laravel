<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Spoowy\Commentable\Models\Comment;

class Comments extends Model
{
    protected $table = 'comments';


    public function getCreatedAtAttribute($value) {
        return time_according_to_user_time_zone($value);
    }


    public function getUpdatedAtAttribute($value) {
        return time_according_to_user_time_zone($value);
    }


    /**
     * Unset fields for json
     *
     * @TODO: Write response macro for clearing comment data
     *
     * @param Collection $data
     * @return Collection
     */
    public static function clearComments($data)
    {
        /**
         * Collection of comments
         */
        if ($data instanceof Collection) {
            foreach ($data as &$comment) {
                self::clearComments($comment);
            }

            return $data;
        }

        /**
         * Processing single comment
         */
        if ($data instanceof Comment) {
            $user = $data->creator()->first();
            $time_ago = new Carbon($data->created_at);
            $user_settings = UserSettings::where('user_id', $user->id)->first();
            $user_settings->avatar = $user_settings->avatar;
            $data['user'] = $user;
            $data['user']['settings'] = $user_settings;
            $data['time_ago'] = $time_ago->diffForHumans();
            $data['is_comment_reply'] = ($data['parent_id'] === null ? false : true);

            /**
             * wrong: some videos have 0:00
             */
            if ($data['video_time'] !== null) {
                $data['video_time_formatted'] = format_duration($data['video_time']);
            }

            if ($data['children']->count() > 0) {

                // Comment have children!
                // Array key `children` is a Collection
                self::clearComments($data['children']);
            }

            /**
             * Unset some data
             */
            unset($data['commentable_id']);
            unset($data['commentable_type']);
            unset($data['creator_type']);
            unset($data['_lft']);
            unset($data['_rgt']);

            return $data;
        }
    }

}
