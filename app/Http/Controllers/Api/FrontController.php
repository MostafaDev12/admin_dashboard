<?php

namespace App\Http\Controllers\Api;

use App\Models\Slider;
use App\Models\Partner;
use App\Models\Service;
use App\Models\Visitor;
use App\Models\PageModel;
use App\Models\Pagesetting;
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



    public function singleService($id)
    {
         
        $data = Service::find($id);
       
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

    public function about_us()
    {
         
        $data = Pagesetting::select('about_title_en', 
        'about_title_fr', 
        'about_title_ar',
        'about_details_en', 
        'about_details_fr', 
        'about_details_ar',
        'about_photo')->first();
      
        return response()->json([
            'status' => true,
              'message' => 'success',
            'data' => $data,

          
        ], 200);
    }

    public function models()
    {
         
        $data = PageModel::get();
      
        return response()->json([
            'status' => true,
              'message' => 'success',
            'data' => $data,

          
        ], 200);
    }
    public function Singlemodel($id)
    {
         
        $data = PageModel::find($id);
      
        return response()->json([
            'status' => true,
              'message' => 'success',
            'data' => $data,

          
        ], 200);
    }
    public function visitors()
    {
         
        $deep_detect = TRUE;
        $ip = 'Visitor';
        $purpose = 'location';
        $output = NULL;
        if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
            $ip = $_SERVER["REMOTE_ADDR"];
            if ($deep_detect) {
                if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
            }
        }
        $purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
        $support    = array("country", "countrycode", "currencycode", "state", "region", "city", "location", "address");
        $continents = array(
            "AF" => "Africa",
            "AN" => "Antarctica",
            "AS" => "Asia",
            "EU" => "Europe",
            "OC" => "Australia (Oceania)",
            "NA" => "North America",
            "SA" => "South America"
        );
        if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
            $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));

            if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
                switch ($purpose) {
                    case "location":
                        $output = array(
                            "city"           => @$ipdat->geoplugin_city,
                            "state"          => @$ipdat->geoplugin_regionName,
                            "country"        => @$ipdat->geoplugin_countryName,
                            "country_code"   => @$ipdat->geoplugin_countryCode,
                            "continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                            "continent_code" => @$ipdat->geoplugin_continentCode,
                            "ip"             => @$ipdat->geoplugin_request,
                            "currency_code"  => @$ipdat->geoplugin_currencyCode
                        );
                        break;
                    case "address":
                        $address = array($ipdat->geoplugin_countryName);
                        if (@strlen($ipdat->geoplugin_regionName) >= 1)
                            $address[] = $ipdat->geoplugin_regionName;
                        if (@strlen($ipdat->geoplugin_city) >= 1)
                            $address[] = $ipdat->geoplugin_city;
                        $output = implode(", ", array_reverse($address));
                        break;
                    case "city":
                        $output = @$ipdat->geoplugin_city;
                        break;
                    case "state":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "region":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "country":
                        $output = @$ipdat->geoplugin_countryName;
                        break;
                    case "countrycode":
                        $output = @$ipdat->geoplugin_countryCode;
                        break;
                    case "currencycode":
                        $output = @$ipdat->geoplugin_currencyCode;
                        break;
                }
            }
        }
        if ($output) {
            if (!empty($output['ip'])) {

                Visitor::firstOrCreate([
                    'ip_address' => $output['ip'],
                ], [
                    'country' => $output['country'],
                    'country_code' => $output['country_code'],
                    'city' => $output['city'],

                ]);

               

            }
        }

      
        return response()->json([
            'status' => true,
              'message' => 'success',
            'data' => $output,

          
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
