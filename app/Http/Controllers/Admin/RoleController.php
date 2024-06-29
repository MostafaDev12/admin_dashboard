<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Validator;

class RoleController extends Controller
{
    public function __construct()
    {
     //   $this->middleware('admin');
    }

    //*** JSON Request
  

    //*** GET Request
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        $datas = Role::where('business_id',$business_id)->orderBy('id','desc')->get();


        return view('admin.role.index',compact('datas'));
    }

    //*** GET Request
    public function create()
    {
        return view('admin.role.create');
    }

    //*** POST Request
    public function store(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');

        //--- Validation Section
        $rules = [
               'photo'      => '',
                ];

        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = new Role();
        $input = $request->all();
        if(!empty($request->section))
        {
            $input['section'] = implode(" , ",$request->section);
        }
        else{
            $input['section'] = '';
           
        }

        $input['business_id'] = $business_id;
        
        $data->fill($input)->save();
        //--- Logic Section Ends
      
        //--- Redirect Section
        $msg = 'New Data Added Successfully.<a href="'.route('admin-role-index').'">View Role Lists.</a>';
        
        return response()->json([

            'status'  => true,
            'msg'   =>   $msg

        ], 200);
        //--- Redirect Section Ends    


    }

    //*** GET Request
    public function edit($id)
    {
        $data = Role::findOrFail($id);
        return view('admin.role.edit',compact('data'));
    }

    //*** POST Request
    public function update(Request $request, $id)
    {
        //--- Validation Section
        $rules = [
               'photo'      => '',
                ];

        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = Role::findOrFail($id);
        $input = $request->all();
        if(!empty($request->section))
        {
            $input['section'] = implode(" , ",$request->section);
        }
        else{
            $input['section'] = '';
        }
        $data->update($input);
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = 'Data Updated Successfully.<a href="'.route('admin-role-index').'">View Role Lists.</a>';
      
        return response()->json([

            'status'  => true,
            'msg'   =>   $msg

        ], 200);
        //--- Redirect Section Ends    

    }

    //*** GET Request Delete
    public function destroy($id)
    {
        $data = Role::findOrFail($id);
        $data->delete();
        //--- Redirect Section     
        $msg = 'Data Deleted Successfully.';
        
        return response()->json([

            'status'  => true,
            'msg'   =>   $msg

        ], 200);   
        //--- Redirect Section Ends     
    }
}
