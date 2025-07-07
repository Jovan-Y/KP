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
    /**
     * Menampilkan halaman utama pengelolaan pengguna (Manajer & Pegawai).
     */
    public function index()
    {
        // Ambil semua pengguna dengan peran 'manager'
        $managers = User::where('role', 'manager')
                        ->orderBy('name')
                        ->get();

        // Ambil semua pengguna dengan peran 'employee', paginasi per 15
        $employees = User::where('role', 'employee')
                         ->orderBy('name')
                         ->paginate(15);
                         
        return view('users.index', compact('managers', 'employees'));
    }

    /**
     * Menyimpan akun baru (Manajer atau Pegawai).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', Rule::in(['manager', 'employee'])],
        ], [
            'name.required' => 'Nama lengkap harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar, silakan gunakan email lain.',
            'password.required' => 'Password harus diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.required' => 'Peran akun harus dipilih.',
        ]);


        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => 'active', 
        ]);

        return back()->with('success', 'Akun berhasil ditambahkan.');
    }

    //Mengubah status akun (aktif/tidak aktif).
    public function updateStatus(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);
        
        $message = "Status akun {$user->name} berhasil diubah menjadi {$newStatus}.";
        return back()->with('success', $message);
    }
}
