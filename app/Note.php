<?php


namespace App;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $table = 'notes';

    protected $fillable = [
        'user_id',
        'author_id',
        'text'
    ];

    protected $hidden = ['author_id'];
}