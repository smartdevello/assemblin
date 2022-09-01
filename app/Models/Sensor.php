<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Sensor extends Model
{
    use HasFactory, Notifiable;
    protected $table = 'sensors';
    protected $fillable = [
        'deviceId', 'observationId', 'tag', 'name', 'type', 'unit', 'value', 'fport', 'message_time', 'point_id', 'point_name', 'visibility'
    ];

    public function point()
    {
        return $this->belongsTo(DEOS_point::class, 'point_id', 'id');
    }

    public function logs()
    { 
        
        return $this->hasOne(SensorLog::class, 'sensor_id', 'id');
    }
}
