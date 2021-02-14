<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoFingerprint extends Model
{
    protected $table = 'video_fingerprints';

    protected $fillable = [
        'id',
        'video_id',
        'fkey',
        'userAgent',
        'webdriver',
        'language',
        'colorDepth',
        'deviceMemory',
        'hardwareConcurrency',
        'screenResolution',
        'availableScreenResolution',
        'timezoneOffset',
        'timezone',
        'sessionStorage',
        'localStorage',
        'indexedDb',
        'addBehavior',
        'openDatabase',
        'platform',
        'plugins',
        'canvas',
        'webgl',
        'webglVendorAndRenderer',
        'adBlock',
        'hasLiedLanguages',
        'hasLiedResolution',
        'hasLiedOs',
        'hasLiedBrowser',
        'touchSupport',
        'fonts',
        'audio',
        'created_at',
        'updated_at',
        'forensic_session_id',
    ];


}
