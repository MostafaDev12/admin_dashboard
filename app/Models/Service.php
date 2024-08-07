<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table ='services';
    
     protected $appends = ['photo'];


    public function getPhotoAttribute()
    {
        return url('/') . '/assets/images/services/' . $this->attributes['photo'];
    }
    protected $fillable = [
        
        'photo',
        'title_ar',
        'title_en',
        'title_fr',
        'details_ar',
        'details_en',
        'details_fr',
       'meta_title_ar',
        'meta_title_en',
        'meta_title_fr',
        'meta_details_ar',
        'meta_details_en',
        'meta_details_fr',
        'tags',
      
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
