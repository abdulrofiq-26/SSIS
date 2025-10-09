<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Encryption\DecryptException;
use Yajra\DataTables\Facades\DataTables;
use Spatie\Permission\Contracts\Role as ContractsRole;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Contracts\Permission as ContractsPermission;
use Spatie\Permission\Models\Permission;
use Carbon\Carbon;
use App\Models\User;

class UserController extends Controller
{
    //
    public function __construct() {
        $this->middleware('auth');
        // $this->middleware('can:setting/manage_account/users.read');
    }

    public function index(Request $request)
    {
        $this->authorize('setting/manage_account/users.read');
        if ($request->isMethod('post')) { //jika menerima method post dari form
            //validasi input form
            $this->validate($request, [ 
                'username'=> ['required', 'string', 'max:255', Rule::unique('users')],
                'name'=> ['required', 'string', 'max:255'],
                'email'=> ['required', 'email', 'max:255', Rule::unique('users')],
                'gender'=> ['required'],
                'new_password' => ['required', 'string', 'min:8'],
                'confirm_password' => ['required', 'string', 'min:8','same:new_password'],
                'roles'=> ['required']
            ]);
            //memasukkan data ke database
            $data = User::create([
                'username' => $request->username,
                'name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
                'password'=> Hash::make($request->new_password),
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now()
            ]);
            //memeberikan roles ke user
            $data->assignRole($request->roles); 
            //kembali ke halaman index
            return redirect()->route('users.index')->with('msg','User "'.$request->name.'" successfully added!');
        }
        //variabel digunakan untuk pilihan Roles
        $roles = Role::where('name',"!=",'admin')->orderBy('name')->get();
        return view('configuration.users.index', compact('roles'));
    }

    public function data(Request $request)
    {
        $this->authorize('setting/manage_account/users.read');
        $data = User::
            with(['roles' => function ($query) {
                $query->select('id', 'name', 'color');
            }])
            ->select('*')->orderBy("name");
            return Datatables::of($data)
                ->filter(function ($instance) use ($request) {
                    //jika pengguna memfilter berdasarkan roles
                    if (!empty($request->get('select_role'))) {
                        $instance->whereHas('roles', function($q) use($request){
                            $q->where('role_id', $request->get('select_role'));
                        });
                    }
                    //jika pengguna memfilter berdasarkan gender
                    if (!empty($request->get('select_gender'))) {
                        $instance->where('gender', $request->get('select_gender'));
                    }
                    //jika pengguna memfilter menggunakan pencarian
                    if (!empty($request->get('search'))) {
                        $instance->where(function($w) use($request){
                            $search = $request->get('search');
                                $w->orWhere('username', 'LIKE', "%$search%")
                                ->orWhere('name', 'LIKE', "%$search%")
                                ->orWhere('email', 'LIKE', "%$search%");
                        });
                    }
                })
                ->addColumn('idd', function($x){
                    //menambahkan kolom idd (id yg ter-enkripsi)
                    return Crypt::encrypt($x['id']);
                })
                ->rawColumns(['idd'])
                ->make(true);
    }

    public function edit ($idd, Request $request)
    {
        $this->authorize('setting/manage_account/users.update');
        //mencoba mendeskripsikan idd menjadi id
        try {
            $id = Crypt::decrypt($idd);
        } catch (DecryptException $e) {
            return redirect()->route('users.index');
        }
        //variabel digunakan untuk pilihan 
        $roles   = Role::orderBy('name')->get();
        $permissions   = Permission::orderBy('name')->get();
        //jika menerima method post dari form
        if ($request->isMethod('post')) {
            $this->validate($request, [ 
                'name' => ['required', 'string'],
                'username'=> ['required', 'string', 'max:255', Rule::unique('users')->ignore($id, 'id')],
                'email'=> ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($id, 'id')],
                'gender' => ['required', 'string'],
            ]);
            //mengubah data user di database
            User::where('id', $id)->update([
                'name'=> $request->name,
                'username'=> $request->username,
                'email'=> $request->email,
                'gender' => $request->gender,
                'updated_at' => Carbon::now()
            ]);
            if(Auth::user()->can('setting/manage_account/users.update-role')){
                //sync ulang roles ke user
                $attach = User::find($id)->syncRoles($request->roles);
                //sync ulang direct permissions ke user
                $set_direct_permissions = User::find($id)->syncPermissions($request->permissions);
            } 
            //mencatat perubahan di log
            Log::info(Auth::user()->name." update user profile #".$id.", ".$request->name);
            //kembali ke halaman edit dengan pesan notifikasi
            return redirect()->route('users.edit', ['id'=>$idd])->with('msg','Profile '.$request->name.' updated successfully!');
        }
        //mencari data user berdasarkan id
        $data = User::find($id);
        return view('configuration.users.edit', compact('data','roles','permissions'));
    }

    public function delete(Request $request) {
        $this->authorize('setting/manage_account/users.delete');
        //tidak memperbolehkan user menghapus dirinya sendiri
        if(Auth::user()->id == $request->id){
            return response()->json([
                'success' => false,
                'message' => 'Access not allowed!'
            ]);
        }
        $user = User::find($request->id);
        //jika user ditemukan
        if($user){
            //mencatat proses penghapusan di log
            Log::warning(Auth::user()->username." deleted user #".$user->id.", username : ".$user->username.", name : ".$user->name);
            //data user dihapus dari database
            $user->delete();
            //jika sukses mengembalikan data dalam bentuk json
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully!'
            ]);
        } else {
            //jika gagal mengembalikan data dalam bentuk json
            return response()->json([
                'success' => false,
                'message' => 'User failed to delete!'
            ]);
        }
    }

    protected function reset_password($idd, Request $request)
    {
        $this->authorize('setting/manage_account/users.reset-password');
        //mencoba mendeskripsikan idd menjadi id
        try {
            $id = Crypt::decrypt($idd);
        } catch (DecryptException $e) {
            return redirect()->route('users.index');
        }
        //mencari data user berdasarkan id
        $data = User::find($id);
        //jika menerima method post dari form
        if ($request->isMethod('post')) {
            $request->validate([
                // 'current_password' => ['required', new MatchOldPassword],
                'new_password' => ['required', 'string', 'min:8'],
                'confirm_password' => ['required','same:new_password'],
            ]);
            //mengubah password user di database
            User::where('id', $id)->update(['password'=> Hash::make($request->new_password)]);
            //mencatat perubahan di log
            Log::warning(Auth::user()->username." reset password user #".$data->id.", username : ".$data->username.", name : ".$data->name);
            return redirect()->route('users.index')->with(['msg' => "$data->name's password has been reset, please try logging in again"]);
        }
        return view('configuration.users.reset_password', compact('data'));
    }
}
