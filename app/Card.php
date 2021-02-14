<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
 

class Card extends Model
{
    protected $table = 'cards';
    protected $fillable = ['user_id','fingerprint','source_id','exp_year','exp_month','card_brand','card_last_four','default_card'];
     
}
