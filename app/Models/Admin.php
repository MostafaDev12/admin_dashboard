<?php

namespace App\Models;

 use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Contracts\Auth\Authenticatable;

class Admin extends  Authenticatable
{
    protected $guard = 'admin';

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role_id', 'photo', 'created_at', 'updated_at', 'remember_token','shop_name','city','access_cities'
    ];

    
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function role()
    {
    	return $this->belongsTo('App\Models\Role');
    }

    /** Identifies the current password without exposing it to the session store. */
    public function sessionFingerprint(): string
    {
        return hash_hmac('sha256', (string) $this->getAuthPassword(), (string) config('app.key'));
    }

    /** Changes the password and invalidates remember-me cookies and other sessions. */
    public function changePassword(string $plainPassword): void
    {
        $this->forceFill(['password' => \Illuminate\Support\Facades\Hash::make($plainPassword)]);
        $this->setRememberToken(\Illuminate\Support\Str::random(60));
        $this->save();
    }

    public function IsSuper(){
        if ($this->id == 1 || $this->role_id == 0) {
           return true;
        }
        return false;
    }

    public function sectionCheck($value){
        
        if($this->id == 1 || $this->role_id == 0){
            
            return true; 
        }
        $sections = explode(" , ", $this->role?->section ?? '');
        if (in_array($value, $sections)){
            return true;
        }else{
            return false;
        }
    }


}
