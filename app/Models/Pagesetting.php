<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pagesetting extends Model
{
    
    protected $fillable = [ 
     'about_title_en', 
     'about_title_fr', 
     'about_title_ar',
     'about_details_en', 
     'about_details_fr', 
     'about_details_ar',
     'about_photo',
   
];

    public $timestamps = false;



    public function getAboutPhotoAttribute()
    {
        return !empty($this->attributes['about_photo']) ? url('/') . '/assets/images/' . $this->attributes['about_photo'] : '';
    }


 

    
    public function upload($name,$file,$oldname)
    {
                $file->move('assets/images',$name);
                if($oldname != null)
                {
                    if (file_exists(public_path().'/assets/images/'.$oldname)) {
                        unlink(public_path().'/assets/images/'.$oldname);
                    }
                }
    }
}
