<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class SensorLog extends Model
{
    use HasFactory, Notifiable;
    protected $table = 'sensor_logs';
    protected $fillable = [
        'sensor_id', 'logs'
    ];
}
