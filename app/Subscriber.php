<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    protected $table = 'subscribers';

    protected $fillable = [
        'user_id',
        'email',
        'created_at',
        'updated_at',
        'project_id',
        'team_id',
        'video_id',
        'firstname',
        'lastname',
        'phone_number',
        'photo_url',
        'facebook_link',
        'facebook_name',
        'linked_in_link',
        'linked_in_name',
        'twitter_link',
        'twitter_name',
        'user_agent',
        'subscription_source',
        'job_title',
        'organization',
        'website',
        'interests',
        'location',
        'gender',
        'details',
    ];

    /**
     * Splits name into firstname, lastname, prefix and returns also original value
     *
     * @param $name
     * @return bool|object
     */
    public function splitName($name)
    {
        preg_match('#^(\w+\.)?\s*([\'\’\w]+)\s+([\'\’\w]+)\s*(\w+\.?)?$#', $name, $results);
        if (isset($results[2]) && isset($results[3])) {
            return (object)[
                'firstname'      => $results[2],
                'lastname'       => $results[3],
                'prefix'         => $results[1],
                'original_value' => $results[0]
            ];
        }

        return false;
    }

    public function getNameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * Subsciber video relation
     *
     * @return HasOne
     */
    public function video()
    {
        return $this->hasOne('App\Video', 'id', 'video_id');
    }

}
