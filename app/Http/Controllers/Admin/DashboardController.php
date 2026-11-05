<?php

namespace App\Http\Controllers\Admin;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use InvalidArgumentException;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Generalsetting;

use App\Models\Blog;
use App\Models\Comment;
use App\Models\User;
use App\Models\Product;
use App\Models\Counter;
use App\Models\Visitor;

class DashboardController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth.admin');
    }

    public function index()
    {

        $days = "";
        $sales = "";

        $visitors = Visitor::get();

        $referrals = Counter::where('type','referral')->orderBy('total_count','desc')->take(5)->get();
        
        $browsers = Counter::where('type','browser')->orderBy('total_count','desc')->take(5)->get();
 
         

        return view('admin.dashboard',compact('referrals','browsers','visitors'));
    }
 
    public function profile()
    {
        $data = Auth::guard('admin')->user();
        return view('admin.profile',compact('data'));
    }

    public function profileupdate(Request $request)
    {
        //--- Validation Section

        $rules =
        [
            'name' => 'sometimes|required|string|max:190',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
            'email' => 'sometimes|required|email|max:190|unique:admins,email,'.Auth::guard('admin')->user()->id,
            'phone' => 'nullable|string|max:40',
        ];


        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends
        // A profile edit must never accept role, password, or other privileged fields.
        $input = $request->only(['name', 'email', 'phone']);
        $data = Auth::guard('admin')->user();
            if ($file = $request->file('photo'))
            {
                $name = (string) \Illuminate\Support\Str::uuid().'.'.$file->extension();
                $file->move('assets/images/admins/',$name);
            $input['photo'] = $name;
            }
        $data->update($input);
        $msg = 'Successfully updated your profile';
        return response()->json($msg);
    }

    public function passwordreset()
    {
        $data = Auth::guard('admin')->user();
        return view('admin.password',compact('data'));
    }

    public function changepass(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if ($request->cpass){
            if (Hash::check($request->cpass, $admin->password)){
                if ($request->newpass == $request->renewpass){
                    $input['password'] = Hash::make($request->newpass);
                }else{
                    return response()->json(array('errors' => [ 0 => 'Confirm password does not match.' ]));
                }
            }else{
                return response()->json(array('errors' => [ 0 => 'Current password Does not match.' ]));
            }
        }
        $admin->update($input);
        $msg = 'Successfully change your passwprd';
        return response()->json($msg);
    }


 

    

 

}
