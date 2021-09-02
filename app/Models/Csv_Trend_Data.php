<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Csv_Trend_Data extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'trend_group_id', 'timestamp', 'sensor_name', 'sensor_value'
    ];

}
