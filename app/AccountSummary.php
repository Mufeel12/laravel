<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountSummary extends Model
{
	protected $table = 'account_summary';
	protected $fillable =
        [
            'user_id',
            'views_monthly_avg',
            'bandwidth_running_avg',
            'bandwidth_usage',
            'videos_views',
            'projects_count',
            'videos_count',
            'contact_size',
            'views_total_watch_time',
            'bandwidth_all_time',
        ];
}
