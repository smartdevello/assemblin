<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TrendGroup extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'controller_id', 'trend_group_name', 'location_name', 'update_interval', 'query_period', 'token'
    ];
}
