<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table ='partners';
    
     protected $appends = ['photo'];


    public function getPhotoAttribute()
    {
        return url('/') . '/assets/images/partners/' . $this->attributes['photo'];
    }
    protected $fillable = [
        
        'photo',
       
      
    ];

    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    
    
     
}
