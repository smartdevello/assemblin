<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DEOS_point extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $fillable = ['id'];
    public $incrementing = false;
    public $timestamps = false;
}
