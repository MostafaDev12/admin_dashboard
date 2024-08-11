<?php

namespace App\Http\Controllers\Admin;
use App\Models\Generalsetting;
use Artisan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Currency;
use App\Models\Contact;
use DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Validator;

class GeneralSettingController extends Controller
{

    protected $rules =
    [
//    /*    'logo'              => 'mimes:jpeg,jpg,png,svg',
//        'favicon'           => 'mimes:jpeg,jpg,png,svg',
//        'loader'            => 'mimes:gif',
//        'admin_loader'      => 'mimes:gif',
//        'affilate_banner'   => 'mimes:jpeg,jpg,png,svg',
//        'error_banner'      => 'mimes:jpeg,jpg,png,svg',
//        'popup_background'  => 'mimes:jpeg,jpg,png,svg',
//        'invoice_logo'      => 'mimes:jpeg,jpg,png,svg',
//        'user_image'        => 'mimes:jpeg,jpg,png,svg',
//        'footer_logo'        => 'mimes:jpeg,jpg,png,svg',
//        'wallet_photo'        => 'mimes:jpeg,jpg,png,svg',
//        'loyalty_photo'        => 'mimes:jpeg,jpg,png,svg',*/
    ];

    public function __construct()
    {
        $this->middleware('auth.admin');
    }


    private function setEnv($key, $value,$prev)
    {
        file_put_contents(app()->environmentFilePath(), str_replace(
            $key . '=' . $prev,
            $key . '=' . $value,
            file_get_contents(app()->environmentFilePath())
        ));
    }

    // Genereal Settings All post requests will be done in this method
    public function generalupdate(Request $request)
    {
        //--- Validation Section
        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        else {
        $input = $request->all();
        $data = Generalsetting::findOrFail(1);


       
            if ($file = $request->file('logo_fr'))
            {
                $name = time().$file->getClientOriginalName();
                $data->upload($name,$file,$data->logo_fr);
                $input['logo_fr'] = $name;
            }
             if ($file = $request->file('logo_en'))
            {
                $name = time().$file->getClientOriginalName();
                $data->upload($name,$file,$data->logo_en);
                $input['logo_en'] = $name;
            }
            
             if ($file = $request->file('logo_ar'))
            {
                $name = time().$file->getClientOriginalName();
                $data->upload($name,$file,$data->logo_ar);
                $input['logo_ar'] = $name;
            }
            
               
            if ($file = $request->file('favicon'))
            {
                $name = time().$file->getClientOriginalName();
                $data->upload($name,$file,$data->favicon);
                $input['favicon'] = $name;
            }
           
            if ($file = $request->file('admin_loader'))
            {
                $name = time().$file->getClientOriginalName();
                $data->upload($name,$file,$data->admin_loader);
                $input['admin_loader'] = $name;
            }
           
           
              if ($file = $request->file('home_video'))
            {
                $name = time().$file->getClientOriginalName();
                $data->uploadvideo($name,$file,$data->home_video);
                $input['home_video'] = $name;
            }
           
             
            
            if(!empty($request->contact_emails)){
                if(in_array(null, $request->contact_emails) )
            {
                $input['contact_emails'] = null;
         
            
            
            }
            else
            {
                $input['contact_emails'] = implode(',', str_replace(',',' ',$request->contact_emails));
           
              
             
            
            }
            }
            
            
            if(!empty($request->emails)){
                if(in_array(null, $request->emails) )
            {
                $input['emails'] = null;
         
            
            
            }
            else
            {
                $input['emails'] = implode(',', str_replace(',',' ',$request->emails));
           
              
             
            
            }
            }
            
            if(!empty($request->phones)){
                if(in_array(null, $request->phones) )
            {
                $input['phones'] = null;
         
            
            
            }
            else
            {
                $input['phones'] = implode(',', str_replace(',',' ',$request->phones));
           
              
             
            
            }
            }
            
            
       $input['is_capcha']  =  $request->is_capcha == 1 ? 1 : 0 ;
        $data->update($input);
        //--- Logic Section Ends


        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        //--- Redirect Section
        $msg = trans('Update Success');
        
        
        return response()->json($msg);  
        // return response()->json([
            
        //     'status'  => true,
        //     'msg'   =>   $msg
            
        // ],200);
        //--- Redirect Section Ends
        }
    }
    
    
    public function photoupdate(Request $request)
    {
        //--- Validation Section
        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        else {
        $input = $request->all();
        $data = Generalsetting::findOrFail(1);
         if ($file = $request->file('wallet_photo'))
            {
                $name = time().$file->getClientOriginalName();
                $data->upload($name,$file,$data->wallet_photo);
                $input['wallet_photo'] = $name;
            }
            if ($file = $request->file('loyalty_photo'))
            {
                $name = time().$file->getClientOriginalName();
                $data->upload($name,$file,$data->loyalty_photo);
                $input['loyalty_photo'] = $name;
            }
            if ($file = $request->file('brandphoto'))
            {
                $name = time().$file->getClientOriginalName();
                $data->upload($name,$file,$data->brandphoto);
                $input['brandphoto'] = $name;
            }

        $data->update($input);
        //--- Logic Section Ends

/*
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
*/
        //--- Redirect Section
         $msg = trans('Update Success');
        
        
        return response()->json([
            
            'status'  => true,
            'msg'   =>   $msg
            
        ],200);
        //--- Redirect Section Ends
        }
    }

    public function generalupdatepayment(Request $request)
    {
        //--- Validation Section
        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        else {
        $input = $request->all();
        $data = Generalsetting::findOrFail(1);
        $prev = $data->molly_key;  
        
        if ($request->vendor_ship_info == ""){
            $input['vendor_ship_info'] = 0;
        }

        if ($request->instamojo_sandbox == ""){
            $input['instamojo_sandbox'] = 0;
        }

        if ($request->paypal_mode == ""){
            $input['paypal_mode'] = 'live';
        }
        else {
            $input['paypal_mode'] = 'sandbox';
        }

        if ($request->paytm_mode == ""){
            $input['paytm_mode'] = 'live';
        }
        else {
            $input['paytm_mode'] = 'sandbox';
        }
        $data->update($input);


        $this->setEnv('MOLLIE_KEY',$data->molly_key,$prev);
        // Set Molly ENV

        //--- Logic Section Ends

        //--- Redirect Section
         $msg = trans('Update Success');
        
        
        return response()->json([
            
            'status'  => true,
            'msg'   =>   $msg
            
        ],200);
        //--- Redirect Section Ends
        }
    }

    public function logo()
    {
        return view('admin.generalsetting.logo');
    }

    public function userimage()
    {
        return view('admin.generalsetting.user_image');
    }

    public function fav()
    {
        return view('admin.generalsetting.favicon');
    }
      public function  bosta()
    
    
    {
        return view('admin.generalsetting.bosta_settings');

    }
     public function load()
    {
        return view('admin.generalsetting.loader');
    }
    
     public function home_video()
    {
        return view('admin.generalsetting.home_video');
    }
  
  
    
     public function contact_messages()
    {
        return view('admin.generalsetting.contact_messages');
    }
  
      public function contact_messages_datatables()
    {
         $datas = Contact::orderBy('id','desc')->get();
         //--- Integrating This Collection Into Datatables
         return Datatables::of($datas)
                            
                            ->rawColumns(['photo','action'])
                            ->toJson(); //--- Returning Json Data To Client Side
    }
    public function  nbe()
    {
        return view('admin.generalsetting.nbe_settings');
    }
 
    public function  fedex()
     
    {
        return view('admin.generalsetting.fedex_settings');
    }
 
 
    public function  fastlo()
     
    {
        return view('admin.generalsetting.fastlo_settings');
    }
    
   public function  aramex()
     
    {
        return view('admin.generalsetting.aramex_settings');
    }

     public function   thwani()
     {
       
        return view('admin.generalsetting.thawany_settings');
     }
    
       public function   fatora()
        {
       
        
          return view('admin.generalsetting.fatora_setting');
        
         }
     
        public function  abs()
        {
       
        
          return view('admin.generalsetting.abs_settings');
        
         }
         
        public function  mylerz()
        {
       
          return view('admin.generalsetting.mylerz_settings');
        
         }
       
      public function paypal()
      {
          return view('admin.generalsetting.paypal_setting');
      }
   
     public function contents()
     {
        return view('admin.generalsetting.websitecontent');
     }
      public function  accept()
      {
        return view('admin.generalsetting.accept_settings');
      }
       public function   fawry()

    {
        return view('admin.generalsetting.fawry_settings');
    }
      
      
     public function  bank()
    {
        return view('admin.generalsetting.bankmisry');
    }
     public function header()
    {
        return view('admin.generalsetting.header');
    }

     public function footer()
    {
        return view('admin.generalsetting.footer');
    }

    public function paymentsinfo()
    {
        return view('admin.generalsetting.paymentsinfo');
    }

    public function affilate()
    {
        return view('admin.generalsetting.affilate');
    }

    public function errorbanner()
    {
        return view('admin.generalsetting.error_banner');
    }

    public function popup()
    {
        return view('admin.generalsetting.popup');
    }
    
    public function templateselect()
    {
        return view('admin.generalsetting.templates');
    }
    
 
    public function maintain()
    {
        return view('admin.generalsetting.maintain');
    }
    
    public function ispopup($status)
    {

        $data = Generalsetting::findOrFail(1);
        $data->is_popup = $status;
        $data->update();
    }


    public function mship($status)
    {

        $data = Generalsetting::findOrFail(1);
        $data->multiple_shipping = $status;
        $data->update();
    }
  public function shipment($status)
    {

        $data = Generalsetting::findOrFail(1);
        $data->shipment = $status;
        $data->update();
    }


    public function mpackage($status)
    {

        $data = Generalsetting::findOrFail(1);
        $data->multiple_packaging = $status;
        $data->update();
    }


    public function paypal5($status)
    {

        $data = Generalsetting::findOrFail(1);
        $data->paypal_check = $status;
        $data->update();
    }


    public function instamojo($status)
    {

        $data = Generalsetting::findOrFail(1);
        $data->is_instamojo = $status;
        $data->update();
    }


    public function paystack($status)
    {

        $data = Generalsetting::findOrFail(1);
        $data->is_paystack = $status;
        $data->update();
    }


    public function paytm($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_paytm = $status;
        $data->update();
    }



    public function molly($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_molly = $status;
        $data->update();
    }

    public function razor($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_razorpay = $status;
        $data->update();
    }



    public function stripe($status)
    {

        $data = Generalsetting::findOrFail(1);
        $data->stripe_check = $status;
        $data->update();
    }

    public function guest($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->guest_checkout = $status;
        $data->update();
    }

    public function isemailverify($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_verification_email = $status;
        $data->update();
    }


    public function cod($status)
    {

        $data = Generalsetting::findOrFail(1);
        $data->cod_check = $status;
        $data->update();
    }

    public function comment($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_comment = $status;
        $data->update();
    }
    public function isaffilate($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_affilate = $status;
        $data->update();
    }

    public function issmtp($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_smtp = $status;
        $data->update();
    }

    public function talkto($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_talkto = $status;
        $data->update();
    }
    public function drift($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_drift = $status;
        $data->update();
    } 
    public function messenger($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_messenger = $status;
        $data->update();
    }

    public function issubscribe($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_subscribe = $status;
        $data->update();
    }

    public function isloader($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_loader = $status;
        $data->update();
    }

    public function stock($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->show_stock = $status;
        $data->update();
    }

    public function ishome($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_home = $status;
        $data->update();
    } 
    public function isshop($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_shop = $status;
        $data->update();
    }

    public function isadminloader($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_admin_loader = $status;
        $data->update();
    }

    public function isdisqus($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_disqus = $status;
        $data->update();
    }

    public function iscontact($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_contact = $status;
        $data->update();
    }
    public function isfaq($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_faq = $status;
        $data->update();
    }
    public function language($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_language = $status;
        $data->update();
    }
    public function currency($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_currency = $status;
        $data->update();
    } 
    public function brands($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_brand = $status;
        $data->update();
    }
    public function regvendor($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->reg_vendor = $status;
        $data->update();
    }

    public function iscapcha($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_capcha = $status;
        $data->update();
    }

    public function isreport($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_report = $status;
        $data->update();
    }

    public function issecure($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_secure = $status;
        $data->update();
    }

    public function ismaintain($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->is_maintain = $status;
        $data->update();
    }
    
    
      public function allowZip($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->allow_zip = $status;
        $data->update();
    }
    
     public function allowShipTo($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->allow_shipto = $status;
        $data->update();
    }
    
     public function Pickup($status)
    {
        $data = Generalsetting::findOrFail(1);
        $data->allow_pickup = $status;
        $data->update();
    }
    
     public function blockIcon()
    {
        return view('admin.generalsetting.block');
    } 
    public function background()
    {
        return view('admin.generalsetting.background');
    } 
    public function about_us()
    {
        return view('admin.generalsetting.about_us');
    }  
    public function home_content()
    {
        return view('admin.generalsetting.home_content');
    } 
}
