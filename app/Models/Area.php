<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Area extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'building_id'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'id');
    }
    public function points()
    {
        return $this->hasMany(DEOS_point::class, 'area_id', 'id');
    }
}
