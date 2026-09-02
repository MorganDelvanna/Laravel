<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'type',
        'data',
        'created_at',
        'emailed',
        'emailed_at',
        'canceled_at',
        'transaction_id'
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'emailed' => 'boolean',
        'emailed_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];
}