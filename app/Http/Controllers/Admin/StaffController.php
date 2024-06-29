<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Models\User as Admin;
use App\Models\Role;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Validator;


class StaffController extends Controller
{
    public function __construct()
    {
    //    $this->middleware('admin');
    }

    //*** JSON Request
 
    //*** GET Request
  	public function index()
    {
        
        $business_id = request()->session()->get('user.business_id');


        $datas = Admin::where('type',2)->where('id','!=',Auth::user()->id)->where('business_id', $business_id)->orderBy('id')->get();

        return view('admin.staff.index',compact('datas'));
    }

    //*** GET Request
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        $roles = Role::where('business_id', $business_id)->get();
        return view('admin.staff.create',compact('roles'));
    }

    //*** POST Request
    public function store(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');

        //--- Validation Section
        $rules = [
               'photo'      => 'required|mimes:jpeg,jpg,png,svg',
                ];

        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = new Admin();
        $input = $request->all();
        if ($file = $request->file('photo')) 
         {      
            $name = time().$file->getClientOriginalName();
            $file->move('assets/images/admins',$name);           
            $input['photo'] = $name;
        } 
        $input['type'] = 2;
        $input['status'] = 1;
        $input['business_id'] = $business_id;
        $input['password'] = bcrypt($request['password']);
        $data->fill($input)->save();
        //--- Logic Section Ends

        //--- Redirect Section        
        $msg = 'New Data Added Successfully.';

        return response()->json([

            'status'  => true,
            'msg'   =>   $msg

        ], 200);    
        //--- Redirect Section Ends    
    }


    public function edit($id)
    {

        $business_id = request()->session()->get('user.business_id');

        $data = Admin::findOrFail($id);  
        $roles = Role::where('business_id', $business_id)->get();
        return view('admin.staff.edit',compact('data','roles'));
    }

    public function update(Request $request,$id)
    {
        //--- Validation Section
        if($id != Auth::user()->id)
        {
            $rules =
            [
                'photo' => 'mimes:jpeg,jpg,png,svg',
                'email' => 'unique:users,email,'.$id
            ];

            $validator = Validator::make($request->all(), $rules);
            
            if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
            //--- Validation Section Ends
            $input = $request->all();  
            $data = Admin::findOrFail($id);        
                if ($file = $request->file('photo')) 
                {              
                    $name = time().$file->getClientOriginalName();
                    $file->move('assets/images/admins/',$name);
                    if($data->photo != null)
                    {
                        if (file_exists(public_path().'/assets/images/admins/'.$data->photo)) {
                            unlink(public_path().'/assets/images/admins/'.$data->photo);
                        }
                    }            
                $input['photo'] = $name;
                } 
            if($request->password == ''){
                $input['password'] = $data->password;
            }
            else{
                $input['password'] = Hash::make($request->password);
            }
            $data->update($input);
            $msg = 'Data Updated Successfully.';
            return response()->json([

                'status'  => true,
                'msg'   =>   $msg
    
            ], 200); 
        }
        else{
            $msg = 'You can not change your role.';
            return response()->json([

                'status'  => false,
                'msg'   =>   $msg
    
            ], 200);        
        }
 
    }

    //*** GET Request
    public function show($id)
    {
        $data = Admin::findOrFail($id);
        return view('admin.staff.show',compact('data'));
    }

    //*** GET Request Delete
    public function destroy($id)
    {
    	
        $data = Admin::findOrFail($id);

        if($data->type == 1 || $data->type == 4 )
    	{
            $msg =  "You don't have access to remove this admin";

            return response()->json([

                'status'  => false,
                'msg'   =>   $msg
    
            ], 200);   
    	}
        //If Photo Doesn't Exist
        if($data->photo == null){
            $data->delete();
            //--- Redirect Section     
            $msg = 'Data Deleted Successfully.';
            return response()->json([

                'status'  => false,
                'msg'   =>   $msg
    
            ], 200);     
            //--- Redirect Section Ends     
        }
        //If Photo Exist
        if (file_exists(public_path().'/assets/images/admins/'.$data->photo)) {
            unlink(public_path().'/assets/images/admins/'.$data->photo);
        }
        $data->delete();
        //--- Redirect Section     
        $msg = 'Data Deleted Successfully.';

        return response()->json([

            'status'  => false,
            'msg'   =>   $msg

        ], 200);     
        //--- Redirect Section Ends    
    }

    
    public function statusupdate($id,$status)
    {
        $user = Admin::findOrFail($id);
        $user->status = $status;
        $user->update();
        //--- Redirect Section     
        $msg = trans('Update Success');
        
        return response()->json([
            'status' => true,
            'msg'   =>  $msg
        ], 200);
        //--- Redirect Section Ends 


    }
}
