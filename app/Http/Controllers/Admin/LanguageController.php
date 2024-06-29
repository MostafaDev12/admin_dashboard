<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DataTables;
use App\Models\Language;
use App;
use Session;
use Illuminate\Support\Str;

class LanguageController extends Controller
{
    public function __construct()
    {
       // $this->middleware('auth:admin');
    }

 
    //*** GET Request
    public function index()
    {
       
        $datas = Language::orderBy('id')->get();

        return view('admin.language.index',compact('datas'));
    }

    //*** GET Request
    public function create()
    {

        $cats = [];
        return view('admin.language.create',compact('cats'));
    }

    //*** POST Request
    public function store(Request $request)
    {
        //--- Validation Section

        //--- Validation Section Ends

        //--- Logic Section
        $new = null;
        $input = $request->all();
        $data = new Language();

        
        if ($file = $request->file('photo')) 
         {      
            $photo = time().$file->getClientOriginalName();
            $file->move('assets/images/language',$photo);           
            $data->photo = $photo;
        } 


        $data->language = $input['language'];
        $name = time().Str::random(8);
        $data->name = $name;
        $data->is_default = '0';
        $data->file = $name.'.json';
        $data->rtl = $input['rtl'];
        $data->save();
        unset($input['_token']);
        unset($input['language']);
        $keys = $request->keys;
        $values = $request->values;
        foreach(array_combine($keys,$values) as $key => $value)
        {
            $n = str_replace("_"," ",$key);
            $new[$n] = $value;
        }  
        $mydata = json_encode($new);

        if (!file_exists(resource_path().'/lang/')) {
            mkdir(resource_path().'/lang/', 0777, true);
        }
         
        file_put_contents(resource_path().'/lang/'.$data->file, $mydata);
        
        //--- Logic Section Ends

        //--- Redirect Section        
        $msg = trans('Add Success');
        
        
        return response()->json([
            'status' => true,
            'url' => url('admins/languages/'),
            'msg'   => $msg
            
        ],200);
        
        //--- Redirect Section Ends    
    }

    //*** GET Request
    public function edit($id)
    {
         

        $langg = Language::findOrFail($id);
        $data_results = file_get_contents(resource_path().'/lang/'.$langg->file);
        $lang = json_decode($data_results, true);
        return view('admin.language.edit',compact('langg','lang'));
    }

    //*** POST Request
    public function update(Request $request, $id)
    {
        //--- Validation Section
        
        //--- Validation Section Ends

        //--- Logic Section
        $new = null;
        $input = $request->all();
        $data = Language::findOrFail($id);
        if (file_exists(resource_path().'/lang/'.$data->file)) {
        //    unlink(public_path().'/assets/lang/'.$data->file);
        }


               
        if ($file = $request->file('photo')) 
        {              
            $photo = time().$file->getClientOriginalName();
            $file->move('assets/images/admins/',$photo);
            if($data->photo != null)
            {
                if (file_exists(public_path().'/assets/images/language/'.$data->photo)) {
                    unlink(public_path().'/assets/images/language/'.$data->photo);
                }
            }            
           $data->photo = $photo;
        } 


        $data->language = $input['language'];
      //  $name = time().Str::random(8);
     //   $data->name = $name;
       
       // $data->file = $name.'.json';
        $data->rtl = $input['rtl'];
        $data->update();
        unset($input['_token']);
        unset($input['language']);
        $keys = $request->keys;
        $values = $request->values;
        foreach(array_combine($keys,$values) as $key => $value)
        {
            $n = str_replace("_"," ",$key);
            $new[$n] = $value;
        }        
        $mydata = json_encode($new);
        file_put_contents(resource_path().'/lang/'.$data->file, $mydata); 
        //--- Logic Section Ends

        //--- Redirect Section     
        $msg = trans('Update Success');
        
        
        return response()->json([
            
            'status'  => true,
            'url' => url('admins/adminlanguages/'),
            'msg'   =>   $msg
            
        ],200);
        
        //--- Redirect Section Ends            
    }

      public function statusupdate($id1,$id2)
        {
            $data = Language::findOrFail($id1);
            $data->is_default = '1';
            $data->update();
            $data = Language::where('_id','!=',$id1)->update(['is_default' => '0']);
            //--- Redirect Section     
           
               $msg = trans('Update Success');
        
               return response()->json([
                   'status' => true,
                   'msg'   =>  $msg
               ], 200);
            //--- Redirect Section Ends  
        }

    //*** GET Request Delete
    public function destroy($id)
    {
         
        $data = Language::findOrFail($id);
        if($data->is_default == '1')
        {
        return "You can not remove default language.";            
        }
        if (file_exists(resource_path().'/lang/'.$data->file)) {
            unlink(resource_path().'/lang/'.$data->file);
        }
        $data->delete();
        //--- Redirect Section     
       $msg = trans('Delete Msg');
        return response()->json([
            'status' => true,
            'msg'   =>  $msg
            ],200);    
        //--- Redirect Section Ends     
    }
    //*** GET Request Delete
    public function change($id)
    {
         
        $data = Language::findOrFail($id);
       
        App::setlocale($data->name);

        session(['admin_language' => $data->name]);
        session(['language_photo' => $data->photo]);
        $language_duraction = $data->rtl == 1 ? 'rtl' :  'ltr';
        session(['language_duraction' => $language_duraction]);
 
      return redirect()->back();
    }

    
}
