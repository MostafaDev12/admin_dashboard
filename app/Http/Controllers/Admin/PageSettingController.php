<?php

namespace App\Http\Controllers\Admin;
use App\Models\Pagesetting;
use Artisan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Currency;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Validator;

class PageSettingController extends Controller
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
    public function pageupdate(Request $request)
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
        $data = Pagesetting::findOrFail(1);


       
            if ($file = $request->file('about_photo'))
            {
                $name = time().$file->getClientOriginalName();
                $data->upload($name,$file,$data->about_photo);
                $input['about_photo'] = $name;
            }
              
           
        $data->update($input);
        //--- Logic Section Ends

 
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
     

    public function aboutUs()
    {
        return view('admin.pagesetting.about_us');
    }

 
     
  
}
