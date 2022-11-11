<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class DEOS_controller extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'deos_controllers';

    protected $fillable = [
        'name', 'ip_address', 'port_number', 'building_id', 'longitude', 'latitude', 'enable_weather_forcast', 'enable_electricityprice_forcast',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'id');
    }

    public function points()
    {

        return $this->hasMany(DEOS_point::class, 'controller_id', 'id');
    }
}