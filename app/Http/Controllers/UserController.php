<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;

class UserController extends Controller
{
public function index(Request $request)
  {
     $query = User::query();

    if ($request->filled('q')) {
      $search = $request->q;
      $query->where('name', 'LIKE', '%' . $search . '%');
    }

    $users = $query->orderBy('id', 'Desc')->paginate(10);

    $users->appends($request->all());
    return view('panel.user.index',compact('users'));
  }

  public function create()
  {
    $roles = Role::all();
    return view('panel.user.create',compact('roles'));
  }


  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required',
      'company' => 'nullable',
      'email' => 'required|email|unique:users,email',
      'phone' => 'nullable|numeric',
      'password' => 'required|min:6',
    ]);

    $user = User::create([
      'name' => $request->name,
      'company' => $request->company,
      'phone' => $request->phone,
      'uuid' => Str::uuid(),
      'email' => $request->email,
      'password' => Hash::make($request->password),
      'status' => $request->status
    ]);
    $user->assignRole($request->role);

    return redirect()->route('castle.user.index')
      ->with('success','Kullanıcı Başarıyla Oluşturuldu!');
  }

  public function edit($uuid)
  {
    $user = User::where('uuid', $uuid)->first();

    if (!$user) {
      return redirect()->route('castle.user.index')
        ->with('error', 'User not found.');
    }
    $roles = Role::all();
    return view('panel.user.edit', compact('user', 'roles'));
  }

  public function update(Request $request, $uuid)
  {
    $user = User::where('uuid', $uuid)->first();

    $request->validate([
      'name' => 'required',
      'email' => 'required|email|unique:users,email,'.$user->id,
      'company' => 'required',
      'status' => 'required|in:1,0',
    ]);

    $user->name = $request->name;
    $user->company = $request->company;
    $user->phone = $request->phone;
    $user->email = $request->email;
    $user->status = $request->status;
    $user->update();

    $user->syncRoles($request->role);

    return redirect()->route('castle.user.index')
      ->with('success', 'Kullanıcı Başarıyla Güncellendi');
  }

    public function destroy($uuid)
    {
        $user = User::where('uuid', $uuid)->first();

        if (!$user) {
            abort(404, "Kullanıcı bulunamadı!");
        }

        $user->delete();

        return redirect()->route('castle.user.index')
            ->with('success', 'Kullanıcı başarıyla silindi!');
    }


  public function search(Request $request)
  {
    $searchTerm = $request->input('q');

    $users = User::where('name', 'like', '%'.$searchTerm.'%')
      ->orWhere('company', 'like', '%'.$searchTerm.'%')
      ->paginate(10);

    return view('panel.user.index', compact('users'));
  }

    public function exportUsers()
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }
}
