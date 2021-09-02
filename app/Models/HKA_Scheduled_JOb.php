<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class HKA_Scheduled_JOb extends Model
{
    use HasFactory, Notifiable;
    protected $table = 'hka_scheduled_jobs';
    protected $fillable = [
        'job_name', 'job_id', 'next_run'
    ];
}
