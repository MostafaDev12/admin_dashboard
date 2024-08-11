<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Generalsetting extends Model
{
    
    protected $fillable = ['logo_en','logo_ar','logo_fr', 
    'contact_emails',
     'favicon', 
     'title_en', 
     'title_fr', 
     'title_ar' 
    ,  'footer_en', 'footer_ar', 'footer_fr'
     ,'phones' ,'emails' ,
    'admin_loader', 'talkto','drift'
    ,'addresses_ar','addresses_en','addresses_fr', 'map', 
   
    'smtp_host','smtp_port','smtp_user','smtp_pass','from_email',
    'from_name' 
     ,'is_verification_email' 
     ,'is_smtp' 
     ,'is_capcha' 
     ,'home_video' 
];

    public $timestamps = false;



    public function getLogoEnAttribute()
    {
        return !empty($this->attributes['logo_en']) ? url('/') . '/assets/images/' . $this->attributes['logo_en'] : '';
    }



    public function getLogoArAttribute()
    {
        return !empty($this->attributes['logo_ar']) ? url('/') . '/assets/images/' . $this->attributes['logo_ar'] : '';
    }



    public function getLogoFrAttribute()
    {
        return !empty($this->attributes['logo_fr']) ? url('/') . '/assets/images/' . $this->attributes['logo_fr'] : '';
    }
    
   public function getHomeVideoAttribute()
    {
        return !empty($this->attributes['home_video']) ? url('/') . '/assets/videos/' . $this->attributes['home_video'] : '';
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
    
    public function uploadvideo($name,$file,$oldname)
    {
                $file->move('assets/videos',$name);
                if($oldname != null)
                {
                    if (file_exists(public_path().'/assets/videos/'.$oldname)) {
                        unlink(public_path().'/assets/videos/'.$oldname);
                    }
                }
    }
}
