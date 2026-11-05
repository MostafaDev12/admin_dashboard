<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use App\Support\SafeUpload;
use Auth;
use DataTables;
use Illuminate\Http\Request;
use Validator;

class StaffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.admin');
    }

    //*** JSON Request
    public function datatables()
    {
        $actor = Auth::guard('admin')->user();
        $datas = Admin::where('id', '!=', 1)->where('id', '!=', $actor->id)->when(! $actor->IsSuper(), fn ($query) => $query->where('role_id', '!=', 0))->orderBy('id')->get();

        //--- Integrating This Collection Into Datatables
        return Datatables::of($datas)
            ->addColumn('role', function (Admin $data) {
                $role = $data->role_id == 0 ? 'No Role' : ($data->role?->name ?? 'No Role');

                return $role;
            })
            ->addColumn('action', function (Admin $data) {
                $delete = '<a href="javascript:;" data-href="'.route('admin-staff-delete', $data->id).'" data-bs-toggle="modal" data-bs-target="#confirm-delete" class="delete  btn btn-sm btn-danger"><i class="las la-trash"></i></a>';

                return '<div class="action-list"><a data-href="'.route('admin-staff-show', $data->id).'" class="view details-width  btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#modal1"> <i class="las la-eye"></i>تفاصيل</a>
                                <a data-href="'.route('admin-staff-edit', $data->id).'" class="edit  btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#modal1"> <i class="las la-edit"></i>تعديل</a>'.$delete.'</div>';
            })
            ->rawColumns(['action'])
            ->toJson(); //--- Returning Json Data To Client Side
    }

    //*** GET Request
    public function index()
    {
        return view('admin.staff.index');
    }

    //*** GET Request
    public function create()
    {
        return view('admin.staff.create');
    }

    //*** POST Request
    public function store(Request $request)
    {
        //--- Validation Section
        $validator = Validator::make($request->all(), [
            'photo' => 'required|file|mimes:jpeg,jpg,png,webp|max:5120',
            'name' => 'required|string|max:190',
            'email' => 'required|email|max:190|unique:admins,email',
            'phone' => 'nullable|string|max:40',
            'password' => 'required|string|min:12|max:200',
            'role_id' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->getMessageBag()->toArray()]);
        }
        //--- Validation Section Ends

        if (! $this->canAssignRole((int) $request->role_id)) {
            return response()->json(['errors' => ['role_id' => ['You cannot assign this role.']]]);
        }

        //--- Logic Section
        $data = new Admin();
        $input = $request->only(['name', 'email', 'phone']);
        $input['photo'] = SafeUpload::move($request->file('photo'), 'assets/images/admins');
        $input['password'] = bcrypt($request->password);
        $input['role_id'] = (int) $request->role_id;
        $data->fill($input)->save();
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = 'New Data Added Successfully.';

        return response()->json($msg);
        //--- Redirect Section Ends
    }

    public function edit($id)
    {
        $data = Admin::findOrFail($id);
        abort_unless($this->canManage($data), 403);

        return view('admin.staff.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        //--- Validation Section
        if ($id != Auth::guard('admin')->user()->id) {
            $data = Admin::findOrFail($id);
            abort_unless($this->canManage($data), 403);

            $validator = Validator::make($request->all(), [
                'photo' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:5120',
                'name' => 'sometimes|required|string|max:190',
                'email' => 'sometimes|required|email|max:190|unique:admins,email,'.$id,
                'phone' => 'nullable|string|max:40',
                'password' => 'nullable|string|min:12|max:200',
                'role_id' => 'sometimes|required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->getMessageBag()->toArray()]);
            }
            //--- Validation Section Ends

            if ($request->has('role_id') && (int) $request->role_id !== (int) $data->role_id && ! $this->canAssignRole((int) $request->role_id)) {
                return response()->json(['errors' => ['role_id' => ['You cannot assign this role.']]]);
            }

            $input = $request->only(['name', 'email', 'phone']);
            if ($request->has('role_id')) {
                $input['role_id'] = (int) $request->role_id;
            }
            if ($file = $request->file('photo')) {
                $input['photo'] = SafeUpload::move($file, 'assets/images/admins');
                SafeUpload::delete('assets/images/admins', $data->getRawOriginal('photo'));
            }
            $data->update($input);
            if ($request->filled('password')) {
                $data->changePassword($request->password);
            }
            $msg = 'Data Updated Successfully.';

            return response()->json($msg);
        } else {
            $msg = 'You can not change your role.';

            return response()->json($msg);
        }

    }

    //*** GET Request
    public function show($id)
    {
        $data = Admin::findOrFail($id);

        return view('admin.staff.show', compact('data'));
    }

    //*** DELETE Request
    public function destroy($id)
    {
        $data = Admin::findOrFail($id);
        abort_unless($this->canManage($data) && (int) $id !== 1 && (int) $id !== (int) Auth::guard('admin')->id(), 403, "You don't have access to remove this admin");
        SafeUpload::delete('assets/images/admins', $data->getRawOriginal('photo'));
        $data->delete();
        //--- Redirect Section
        $msg = 'Data Deleted Successfully.';

        return response()->json($msg);
        //--- Redirect Section Ends
    }

    /** Only owners may change other owners, including the protected first administrator. */
    private function canManage(Admin $target): bool
    {
        return Auth::guard('admin')->user()->IsSuper() || ! $target->IsSuper();
    }

    /** Owners assign any role. Others cannot create owners or grant permissions they do not hold. */
    private function canAssignRole(int $roleId): bool
    {
        $actor = Auth::guard('admin')->user();
        if ($roleId === 0) {
            return $actor->IsSuper();
        }
        $role = Role::find($roleId);
        if (! $role) {
            return false;
        }
        if ($actor->IsSuper()) {
            return true;
        }
        $requested = array_filter(array_map('trim', explode(',', (string) $role->section)));

        return collect($requested)->every(fn ($section) => $actor->sectionCheck($section));
    }
}
