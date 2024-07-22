<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $table = 'visitors';

    protected $fillable = ['ip_address', 'country', 'city', 'country_code'];

   // public $timestamps = false;
}
