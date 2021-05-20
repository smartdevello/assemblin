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
        'deviceId', 'observationId', 'tag', 'name', 'type', 'unit', 'value', 'message_time', 'point_id', 'point_name', 'controller_id', 'area_id'
    ];

    public function point()
    {
        return $this->belongsTo(DEOS_point::class, 'point_id', 'id');
    }
    public function controller()
    {
        return $this->belongsTo(DEOS_controller::class, 'controller_id', 'id');
    }
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id', 'id');
    }
}
