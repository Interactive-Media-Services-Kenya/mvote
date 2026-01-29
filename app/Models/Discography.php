<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discography extends Model
{
    protected $fillable = [
        'artist_id',
        'title',
        'release_year',
        'description',
        'link'
    ];

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }}
