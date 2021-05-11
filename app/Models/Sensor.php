<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    use HasFactory;
    protected $table = 'sensors';
    protected $fillable = ['deviceId', 'sensorId', 'tag', 'name', 'type', 'unit', 'value', 'message_time', 'DEOS_pointId'];
    public $timestamps = false;
}
