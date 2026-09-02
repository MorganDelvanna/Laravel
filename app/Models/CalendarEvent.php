<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'allDay' => 'boolean',
    ];

    protected $fillable = [
        'start',
        'end',
        'title',
        'description',
        'allDay'
    ];
}