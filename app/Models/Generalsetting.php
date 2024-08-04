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
    ,'copyright','copyright_ar','phones' ,'emails' ,
    'admin_loader', 'talkto','drift'
    ,'addresses_ar','addresses_en','addresses_fr', 'map', 
   
    'smtp_host','smtp_port','smtp_user','smtp_pass','from_email',
    'from_name' 
     ,'is_verification_email' 
];

    public $timestamps = false;


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
