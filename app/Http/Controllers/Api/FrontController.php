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
use App\Models\Contact;
use Validator;
use App\Classes\GeniusMailer;

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
         
        
         $data = [];
         
        $lang = request()->header('Accept-Language');
          
        $datas = Slider::get();
      
      foreach($datas as $k=>$dat){
          
         $data[$k]['title'] = $dat->{'title_'.$lang} ; 
         $data[$k]['details'] =  $dat->{'details_'.$lang} ; 
         
         $data[$k]['id'] = $dat->id  ; 
         $data[$k]['photo'] = $dat->photo  ; 
      }
      
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
         
         $data = [];
         
        $lang = request()->header('Accept-Language');
          
        $datas = Service::get();
      
      foreach($datas as $k=>$dat){
           $data[$k]['id'] = $dat->id  ; 
         $data[$k]['title'] = $dat->{'title_'.$lang} ; 
         $data[$k]['details'] =  $dat->{'details_'.$lang} ; 
         $data[$k]['meta_title'] =  $dat->{'meta_title_'.$lang} ; 
         $data[$k]['meta_details'] = $dat->{'meta_details_'.$lang}  ; 
         $data[$k]['tags'] = $dat->tags  ; 
         $data[$k]['photo'] = $dat->photo  ; 
      }
      
        return response()->json([
            'status' => true,
              'message' => 'success',
            'data' => $data,

          
        ], 200);
    }



    public function singleService($id)
    {
         
        $dat = Service::find($id);
       
       
       
        $data = [];
         
        $lang = request()->header('Accept-Language');
          $data['id'] = $dat->id  ; 
         $data['title'] = $dat->{'title_'.$lang} ; 
         $data['details'] =  $dat->{'details_'.$lang} ; 
         $data['meta_title'] =  $dat->{'meta_title_'.$lang} ; 
         $data['meta_details'] = $dat->{'meta_details_'.$lang}  ; 
         $data['tags'] = $dat->tags  ; 
         $data['photo'] = $dat->photo  ; 
       
        return response()->json([
            'status' => true,
              'message' => 'success',
            'data' => $data,

          
        ], 200);


    }

    public function settings()
    {
         
        $dat = Generalsetting::first();
      
      
        $lang = request()->header('Accept-Language');
           
        $data['logo'] = $dat->{'logo_'.$lang} ; 
        $data['title'] =  $dat->{'title_'.$lang} ; 
        $data['footer'] =  $dat->{'footer_'.$lang} ; 
        $data['addresses'] =  json_decode($dat->{'addresses_'.$lang}) ; 
        
        $data['favicon'] = $dat->favicon  ; 
        $data['phones'] =  explode(',',$dat->phones)  ; 
        $data['emails'] =   explode(',',$dat->emails) ; 
        $data['contact_emails'] =  explode(',',$dat->contact_emails)   ; 
        $data['map'] = $dat->map  ;
      
        return response()->json([
            'status' => true,
              'message' => 'success',
            'data' => $data,

          
        ], 200);
    }

    public function about_us()
    {
         
        $dat = Pagesetting::select('about_title_en', 
        'about_title_fr', 
        'about_title_ar',
        'about_details_en', 
        'about_details_fr', 
        'about_details_ar',
        'about_photo')->first();
      
      
      
        $data = [];
         
        $lang = request()->header('Accept-Language');
           
         $data['title'] = $dat->{'about_title_'.$lang} ; 
         $data['details'] =  $dat->{'about_details_'.$lang} ; 
         
         $data['photo'] = $dat->about_photo  ; 
       
      
        return response()->json([
            'status' => true,
              'message' => 'success',
            'data' => $data,

          
        ], 200);
    }

    public function portfolio()
    {
         
        $dat = Pagesetting::select('portfolio_title_en', 
        'portfolio_title_fr', 
        'portfolio_title_ar',
        'portfolio_details_en', 
        'portfolio_details_fr', 
        'portfolio_details_ar',
        'portfolio_photo')->first();
      
      
      
        $data = [];
         
        $lang = request()->header('Accept-Language');
           
         $data['title'] = $dat->{'portfolio_title_'.$lang} ; 
         $data['details'] =  $dat->{'portfolio_details_'.$lang} ; 
         
         $data['photo'] = $dat->portfolio_photo  ; 
       
      
        return response()->json([
            'status' => true,
              'message' => 'success',
            'data' => $data,

          
        ], 200);
    }

    public function models()
    {
         
       
       $data = [];
         
        $lang = request()->header('Accept-Language');
          
        $datas = PageModel::get();
      
      foreach($datas as $k=>$dat){
           $data[$k]['id'] = $dat->id  ; 
         $data[$k]['title'] = $dat->{'title_'.$lang} ; 
         $data[$k]['details'] =  $dat->{'details_'.$lang} ; 
         
         $data[$k]['photo'] = $dat->photo  ; 
      }
      
        return response()->json([
            'status' => true,
              'message' => 'success',
            'data' => $data,

          
        ], 200);
    }
    public function Singlemodel($id)
    {
         
        $dat = PageModel::find($id);
      
      
        $data = [];
         
        $lang = request()->header('Accept-Language');
         $data['id'] = $dat->id  ; 
         $data['title'] = $dat->{'title_'.$lang} ; 
         $data['details'] =  $dat->{'details_'.$lang} ; 
         
         $data['photo'] = $dat->photo  ; 
       
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


       //Send email to admin
       public function contactSubmit(Request $request)
       {
           $gs = Generalsetting::findOrFail(1);
            $ps = Pagesetting::find(1);
   
   
   
    $rules = [
               'name'      => 'required',
               'phone'      => 'required',
               'email'      => 'email',
                ];

        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
   
        //    if($gs->is_capcha == 1)
        //    {
   
        //    // Capcha Check
        //    $value = session('captcha_string');
        //    if ($request->codes != $value){
        //        return response()->json(array('errors' => [ 0 => 'Please enter Correct Capcha Code.' ]));
        //    }
   
        //    }
   
     
           $name = $request->name ;
           $phone = $request->phone ;
           $job = $request->job ;
           $from = $request->email ;
           $message = $request->message ;
           $subject_title = $request->subject ;
   
           
               $subject = "Email From Of ".$request->name ;
   
               $msg = "Name: ".$name."\nEmail: ".$from."\nPhone: ".$phone."\nSubject: ".$subject_title ."\nMessage: ".$message;
         
           
            
          
          if(!empty($gs->contact_emails)){
   
   
               $to =    explode(',', $gs->contact_emails);
               
              
               
        
           foreach($to as $key => $data1){
   
         
               if($gs->is_smtp == 1)
               {
               $data = [
                   'to' => $to[$key],
                   'subject' => $subject,
                   'body' => $msg,
               ];
       
               $mailer = new GeniusMailer();
               $mailer->sendCustomMail($data);
               }
               else
               {
               $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
               mail($to[$key],$subject,$msg,$headers);
               }
               // Login Section Ends
       
       
           }
   
           
           } 
    
        $data =   Contact::create([
               'name' => $name ,
               'phone' => $phone ,
               'email' => $from ,
               'subject' => $subject_title , 
               'message' => $message ,
           ]);

           // Redirect Section
           return response()->json([
            'status' => true,
              'message' => 'success',
              'data' =>  $data,
            
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
