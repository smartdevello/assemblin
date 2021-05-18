<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class DeosPoint extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'sensor', 'controller_id'
    ];

    public function controller()
    {
        return $this->belongsTo(DEOS_controller::class, 'controller_id', 'id');
    }
}
