<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kandang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('kandang')->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $kandangs = Kandang::where('status', 'aktif')->get();
        return view('users.create', compact('roles', 'kandangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => 'required|string|unique:users|max:100',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|string|min:6|confirmed',
            'role'      => 'required|in:pemilik,pekerja',
            'kandang_id' => 'nullable|exists:kandang,id',
        ]);

        $user = User::create([
            'name'       => $request->name,
            'username'   => $request->username,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => $request->role,
            'kandang_id' => $request->role === 'pekerja' ? $request->kandang_id : null,
        ]);

        // Assign role
        $user->assignRole($request->role);

        return redirect()->route('users.index')
                         ->with('success', 'User berhasil ditambahkan!');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        $kandangs = Kandang::where('status', 'aktif')->get();
        return view('users.edit', compact('user', 'roles', 'kandangs'));
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => 'required|string|max:100|unique:users,username,' . $user->id,
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'role'      => 'required|in:pemilik,pekerja',
            'kandang_id' => 'nullable|exists:kandang,id',
        ]);

        $user->update([
            'name'       => $request->name,
            'username'   => $request->username,
            'email'      => $request->email,
            'role'       => $request->role,
            'kandang_id' => $request->role === 'pekerja' ? $request->kandang_id : null,
        ]);

        // Sync role
        $user->syncRoles($request->role);

        return redirect()->route('users.index')
                         ->with('success', 'User berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        // Jangan bisa hapus diri sendiri
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                             ->with('error', 'Tidak bisa menghapus user sendiri!');
        }

        $user->delete();

        return redirect()->route('users.index')
                         ->with('success', 'User berhasil dihapus!');
    }
}
