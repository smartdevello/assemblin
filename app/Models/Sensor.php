<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $fillable = ['id'];
    public $incrementing = false;
}
