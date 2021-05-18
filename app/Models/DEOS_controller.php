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
        'name', 'area_id'
    ];

}
