<?php

namespace App\Http\Controllers\Admin;
use App\Models\Pagesetting;
use Artisan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Currency;
use App\Http\Controllers\Controller;
use App\Support\SafeUpload;
use Illuminate\Support\Facades\Input;
use Validator;

class PageSettingController extends Controller
{

    protected $rules =
    [
        'about_photo'     => SafeUpload::IMAGE_RULE,
        'portfolio_photo' => SafeUpload::IMAGE_RULE,
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
        $input = $request->except(array_keys($this->rules));
        $data = Pagesetting::findOrFail(1);


       
            if ($file = $request->file('about_photo'))
            {
                $name = SafeUpload::move($file, 'assets/images');
                $input['about_photo'] = $name;
            }
              
            if ($file = $request->file('portfolio_photo'))
            {
                $name = SafeUpload::move($file, 'assets/images');
                $input['portfolio_photo'] = $name;
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

 
    public function portfolio()
    {
        return view('admin.pagesetting.portfolio');
    }

 
     
  
}
