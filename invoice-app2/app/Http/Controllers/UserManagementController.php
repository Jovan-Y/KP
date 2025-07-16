<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{

    public function index()
    {
        //ambil semua pengguna sesuai peran
        $managers = User::where('role', 'manager')
                        ->orderBy('name')
                        ->get();

        $employees = User::where('role', 'employee')
                         ->orderBy('name')
                         ->paginate(15);
                                 
        return view('users.index', compact('managers', 'employees'));
    }

    //fungsi menyimpan akun
    public function store(Request $request)
    {
        // 1. validasi
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class], 
            'password' => ['required', 'confirmed', Rules\Password::defaults()], 
            'role' => ['required', Rule::in(['manager', 'employee'])],
        ], [
            // pesan error kustom
            'name.required' => 'Nama lengkap harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar, silakan gunakan email lain.',
            'password.required' => 'Password harus diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.required' => 'Peran akun harus dipilih.',
        ]);

        // 2. buat akun baru di database.
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), 
            'role' => $request->role,
            'status' => 'active', 
        ]);

        return back()->with('success', 'Akun berhasil ditambahkan.');
    }

    //fungsi mengubah status akun
    public function updateStatus(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        //membalik status
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);
        $message = "Status akun {$user->name} berhasil diubah menjadi {$newStatus}.";

        return back()->with('success', $message);
    }
}
