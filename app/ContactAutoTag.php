<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ContactAutoTag extends Model
{
    protected $table = 'contact_auto_tags';
    protected $fillable = ['user_id', 'title', 'tag', 'contact_filter', 'push_tag_able', 'tag_color', 'tag_action', 'active', 'completed'];

    protected $hidden = ['user'];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function conditions()
    {
        return $this->hasMany('App\ContactAutoTagCondition', 'auto_tag_id', 'id');
    }

    public static function searchTags($searchData, $statusUser)
    {
        $filteredTags = ContactAutoTag::select([
                'contact_auto_tags.tag as tag',
                'contact_auto_tags.id as id',
                DB::raw("(SELECT count(*) FROM users
                LEFT JOIN contact_auto_tags cat ON cat.user_id = users.id
                LEFT JOIN subscriptions ON subscriptions.user_id = users.id
                WHERE cat.tag = contact_auto_tags.tag AND users.status_id = $statusUser) 
                as usersCount"),
            ])
            ->leftJoin('users', 'contact_auto_tags.user_id', 'users.id')
            ->leftJoin('subscriptions', 'subscriptions.user_id', '=', 'users.id')

            ->when(isset($searchData['tag']), function ($query) use($searchData) {
                $tag = $searchData['tag'];
                $query->where('tag', 'LIKE', "%$tag%");
            })
            ->when(isset($searchData['offset']), function ($query) use($searchData) {
                $query->offset($searchData['offset']);
            })
            ->when(isset($searchData['limit']), function ($query) use($searchData) {
                $query->limit($searchData['limit']);
            })
            ->when($statusUser, function ($query, $statusUser) {
                $query->where('users.status_id', '=', $statusUser);
            })
            ->distinct('contact_auto_tags.tag')
            ->groupBy('contact_auto_tags.tag')
            ->orderBy('usersCount', 'desc')
            ->get();

        return [
            'allTagCount' => count($filteredTags),
            'filteredTags' => $filteredTags,
        ];
    }
}
