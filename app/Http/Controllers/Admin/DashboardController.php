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
        // The profile screen holds the password form; this view never existed.
        return redirect()->route('admin.profile');
    }

    public function changepass(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $data = $request->validate([
            'cpass' => 'required|string',
            'newpass' => 'required|string|min:12|max:200',
            'renewpass' => 'required|same:newpass',
        ]);
        if (!Hash::check($data['cpass'], $admin->password)) {
            return response()->json(['errors' => ['cpass' => ['Current password does not match.']]], 422);
        }
        $admin->changePassword($data['newpass']);
        // Keep this session signed in; every other session for the account is ended.
        $request->session()->put('admin_session_'.$admin->getAuthIdentifier(), $admin->sessionFingerprint());
        $msg = 'Successfully change your passwprd';
        return response()->json($msg);
    }


 

    

 

}
