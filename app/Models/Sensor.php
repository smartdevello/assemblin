<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Sensor extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'deviceId', 'observationId', 'tag', 'name', 'type', 'unit', 'value', 'message_time', 'deos_pointId'
    ];

    public $timestamps = false;
}
