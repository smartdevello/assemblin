<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Building extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'location_id', 'img_url'
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }

    public function areas()
    {
        return $this->hasMany(Area::class, 'building_id', 'id');
    }

    public function controllers()
    {
        return $this->hasMany(DEOS_controller::class, 'building_id', 'id');
    }
}
