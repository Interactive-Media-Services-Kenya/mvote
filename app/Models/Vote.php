<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    public $fillable = ['user_id', 'artist_id', 'vote', 'comment'];
}
