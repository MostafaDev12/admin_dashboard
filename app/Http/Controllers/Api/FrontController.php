<?php

namespace App\Http\Controllers\Api;

use App\Models\Slider;
use App\Models\Partner;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use App\Models\Generalsetting;

class FrontController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
       // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function sliders()
    {
         
        $data = Slider::get();
      
        return response()->json([
            'status' => true,
              'message' => 'success',
            'data' => $data,

          
        ], 200);
    }

    public function partners()
    {
         
        $data = Partner::get();
      
        return response()->json([
            'status' => true,
              'message' => 'success',
            'data' => $data,

          
        ], 200);
    }

    public function services()
    {
         
        $data = Service::get();
      
        return response()->json([
            'status' => true,
              'message' => 'success',
            'data' => $data,

          
        ], 200);
    }

    public function settings()
    {
         
        $data = Generalsetting::first();
      
        return response()->json([
            'status' => true,
              'message' => 'success',
            'data' => $data,

          
        ], 200);
    }

    public function root()
    {
        return view('index');
    }

    /*Language Translation*/
    public function lang($locale)
    {
        if ($locale) {
            App::setLocale($locale);
            Session::put('lang', $locale);
            Session::save();
            return redirect()->back()->with('locale', $locale);
        } else {
            return redirect()->back();
        }
    }

}
