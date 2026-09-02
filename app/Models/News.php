<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $fillable = [
        'publish_date',
        'header',
        'description',
        'archived'
    ];
    protected $casts = [
        'publish_date' => 'date',
        'archived' => 'boolean',
    ];

}