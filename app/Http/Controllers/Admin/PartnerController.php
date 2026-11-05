<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Models\Partner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Support\SafeUpload;
use Illuminate\Support\Facades\Input;
use Validator;

class PartnerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.admin');
    }

    //*** JSON Request
    public function datatables()
    {
         $datas = Partner::orderBy('id','desc')->get();
         //--- Integrating This Collection Into Datatables
         return Datatables::of($datas)
                            ->editColumn('photo', function(Partner $data) {
                                $photo =  $data->photo;
                              
                                return  '<div><img style="width:200px;height:100px" src="'.$photo.'"></div>';
                            })
                            ->addColumn('action', function(Partner $data) {
                                return '<div class="action-list">
                                <a class=" btn btn-sm btn-secondary" href="' . route('admin-partners-edit',$data->id) . '"> <i class="las la-edit"></i>تعديل</a>
                                <a href="javascript:;" data-href="' . route('admin-partners-delete',$data->id) . '" data-bs-toggle="modal" data-bs-target="#confirm-delete" class="delete  btn btn-sm btn-danger"><i class="las la-trash"></i></a>
                                </div>';
                            }) 
                            ->rawColumns(['photo','action'])
                            ->toJson(); //--- Returning Json Data To Client Side
    }

    //*** GET Request
    public function index()
    {
        return view('admin.partners.index');
    }

    //*** GET Request
    public function create()
    {
        return view('admin.partners.create');
    }

    //*** POST Request
    public function store(Request $request)
    {
        //--- Validation Section
        $rules = [
               'photo'      => SafeUpload::IMAGE_RULE,
                ];

        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = new Partner();
        $input = $request->except('photo');
        
        if ($file = $request->file('photo')) 
        {              
            $name = SafeUpload::move($file, 'assets/images/partners');
                    
        $input['photo'] = $name;
        } 
        $data->fill($input)->save();
        //--- Logic Section Ends
      
        //--- Redirect Section
        $msg = 'New Data Added Successfully.<a href="'.route('admin-partners-index').'">View partners Lists.</a>';
       //   return redirect(route('admin-partners-index'))->with($msg);
      return response()->json($msg);
        //--- Redirect Section Ends    


    }

    //*** GET Request
    public function edit($id)
    {
        $data = Partner::findOrFail($id);
        return view('admin.partners.edit',compact('data'));
    }

    //*** POST Request
    public function update(Request $request, $id)
    {
        //--- Validation Section
        $rules = [
               'photo'      => SafeUpload::IMAGE_RULE,
                ];

        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = Partner::findOrFail($id);
        $input = $request->except('photo');
        if ($file = $request->file('photo')) 
        {              
            $name = SafeUpload::move($file, 'assets/images/partners');
        $input['photo'] = $name;
        } 
        $data->update($input);
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = 'Data Updated Successfully.<a href="'.route('admin-partners-index').'">View partners Lists.</a>';
        return response()->json($msg);
        //--- Redirect Section Ends    

    }

    //*** DELETE Request
    public function destroy($id)
    {
        $data = Partner::findOrFail($id);
        $data->delete();
        //--- Redirect Section     
        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);      
        //--- Redirect Section Ends     
    }
}
