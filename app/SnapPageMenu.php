<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SnapPageMenu extends Model
{
    protected $fillable = [
        'snap_page_id',
        'menus',
    ];
}
