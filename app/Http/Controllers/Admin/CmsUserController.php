<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CmsUserController extends Controller
{
    public function index()
    {
        abort_unless(auth('admin')->user()->IsSuper(), 403);

        return view('cms.users', ['admins' => Admin::with('role')->paginate(20)]);
    }

    public function store(Request $request)
    {
        abort_unless(auth('admin')->user()->IsSuper(), 403);
        $data = $request->validate(['name' => 'required|string|max:190', 'email' => 'required|email|unique:admins,email', 'password' => 'required|string|min:12|max:200', 'access' => 'required|in:editor,owner']);
        $role = Role::firstOrCreate(['name' => 'Website content editor', 'section' => 'cms']);
        Admin::create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password']), 'role_id' => $data['access'] === 'owner' ? 0 : $role->id]);

        return back()->with('success', 'Administrator created.');
    }

    public function update(Request $request, Admin $admin)
    {
        abort_unless(auth('admin')->user()->IsSuper(), 403);
        $data = $request->validate(['name' => 'required|string|max:190', 'email' => ['required', 'email', Rule::unique('admins', 'email')->ignore($admin->id)], 'password' => 'nullable|string|min:12|max:200']);
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }
        $admin->update($data);

        return back()->with('success', 'Administrator updated.');
    }

    public function destroy(Admin $admin)
    {
        abort_unless(auth('admin')->user()->IsSuper(), 403);
        abort_if($admin->id === auth('admin')->id() || $admin->id === 1, 422, 'This administrator cannot be deleted.');
        $admin->delete();

        return back()->with('success', 'Administrator deleted.');
    }
}
