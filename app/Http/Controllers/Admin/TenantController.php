<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
 
use Illuminate\Support\Facades\Input;
use Validator;
use DB;
use Carbon\Carbon;

class TenantController extends Controller
{
    public function __construct()
    {
       // $this->middleware('admin');
    }

    //*** JSON Request
    

    //*** GET Request
    public function index()
    {
        $datas = Tenant::orderBy('created_at')->get();

        return view('admin.tenant.index',compact('datas'));
    }



    //*** GET Request
    public function show($id)
    {
    }

    //*** GET Request
    public function create()
    {

        return view('admin.tenant.create');
    }

    //*** POST Request
    public function store(Request $request)
    {
        //--- Validation Section
        $rules = [
            'name'      => 'required',
            'domain'      => 'required',
            'database'      => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = new Tenant();
        $input = $request->all();
        $input['status'] = 1;
        $input['created_at'] = Carbon::now();
        $data->fill($input)->save();
        //--- Logic Section Ends
        if(!empty($request->migrate) && $request->migrate == 1) {

       $this->migrateTenant($data->database);

        }
 
        //--- Redirect Section        
        $msg = 'New Data Added Successfully.';
        return response()->json([

            'status'  => true,
            'msg'   =>   $msg

        ], 200);
        //--- Redirect Section Ends   


    }

  private function migrateTenant($database)
    {
        Config::set('database.connections.mongodb.database', $database);
        DB::purge('mongodb'); // Reset the MongoDB connection
        DB::reconnect('mongodb'); // Reconnect to the new tenant database
 
        \Artisan::call('migrate', [
            '--database' => 'mongodb', // Specify the connection
            '--path' => 'database/migrations', // Adjust the path based on your migration folder structure
        ]);

        
    }
    //*** GET Request    
    public function edit($id)
    {
        $data = Tenant::findOrFail($id);
        return view('admin.tenant.edit', compact('data'));
    }

    //*** POST Request
    public function update(Request $request, $id)
    {
        //--- Validation Section
        $rules = [
            'name'      => 'required',
            'domain'      => 'required',
            'database'      => 'required',
        ];

        $messages = [];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->messages(),

            ], 200);
        }
        //--- Validation Section Ends

        $user = Tenant::findOrFail($id);
        $data = $request->all();
 
        $user->update($data);
        $msg = trans('Update Success');


        return response()->json([

            'status'  => true,
            'msg'   =>   $msg

        ], 200);
    }

    //*** GET Request Delete
    public function destroy($id)
    {
        $user = Tenant::findOrFail($id);


        $user->delete();
        //--- Redirect Section     
        $msg = trans('Delete Msg');
        return response()->json([
            'status' => true,
            'msg'   =>  $msg
        ], 200);
        //--- Redirect Section Ends 


    }    
    
    public function statusupdate($id,$status)
    {
        $user = Tenant::findOrFail($id);
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
