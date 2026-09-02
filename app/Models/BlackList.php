<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlackList extends Model
{
    protected $fillable = [
        'email'
    ];
    protected $table = 'blacklist';

    public $timestamps = false;

}