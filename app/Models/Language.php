<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 

class Language extends Model
{
    public $timestamps = false;

    
 
    public function getPhotoAttribute()
    {
        return !empty($this->attributes['photo']) ? url('/') . '/assets/images/language/' . $this->attributes['photo'] : '';
    }

}
