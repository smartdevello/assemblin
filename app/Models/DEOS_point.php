<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class DEOS_point extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'deos_points';
    protected $fillable = [
        'name', 'label', 'type', 'meta_property', 'meta_room', 'meta_sensor', 'meta_type', 'value', 'controller_id'
    ];

    public function controller()
    {
        return $this->belongsTo(DEOS_controller::class, 'controller_id', 'id');
    }
}
