<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Intervention\Image\ImageManagerStatic;

class Stage extends Model
{
	protected $table = 'stages';
	
	protected $fillable = [
		'user_id', 'about_title', 'about_description', 'cover_image', 'first_visit', 'show_website', 'website', 'show_phone_number', 'phone_number',
		'show_email', 'email', 'show_facebook', 'facebook', 'show_instagram', 'instagram', 'show_twitter', 'twitter',
	];
	
	protected $hidden = [
		'created_at', 'updated_at', 'user'
	];
	
	public static function createDefaultStage($user)
	{
		$stage = self::where('user_id', $user->id)->first();
		if (!$stage) {
			$stage = new Stage();
		}
		$stage->user_id = $user->id;
		$stage->email = $user->email;
		$stage->phone_number = $user->phone;
		$stage->save();

		return $stage;
	}
	
	public function user()
	{
		return $this->belongsTo('App\User');
	}


	public static function imageOptimize($file_path, $file_name, $quality = null)
    {
        $img = ImageManagerStatic::make(public_path($file_path . $file_name));
        $size = $img->filesize();

        $megabytes = ceil($size / 1024 / 1024);

        if (is_null($quality)){
            $quality = $megabytes > 2
                ? ($megabytes < 5 ? 70 : ($megabytes < 10 ? 40 : 10))
                : 90;
        }

        $img->save(public_path($file_path . $file_name), $quality);
    }
}
