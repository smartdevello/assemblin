<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Location extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'img_url', 'enable_kiona_endpoint'
    ];

    public function buildings()
    {
        return $this->hasMany(Building::class, 'location_id', 'id');
    }
}
