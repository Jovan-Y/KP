<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    // Menampilkan halaman utama pengelolaan pegawai
    public function index()
    {
        // Ambil semua pengguna dengan peran 'employee', paginasi per 15
        $employees = User::where('role', 'employee')
                            ->orderBy('name')
                            ->paginate(15);
                            
        return view('users.index', compact('employees'));
    }

    // Menyimpan akun pegawai baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'employee',
            'status' => 'active', // Default status saat dibuat
        ]);

        return back()->with('success', 'Akun pegawai berhasil ditambahkan.');
    }

    // Mengubah status (aktif/tidak aktif)
    public function updateStatus(User $user)
    {
        // Pastikan manajer tidak bisa menonaktifkan akunnya sendiri
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);
        
        $message = "Status akun {$user->name} berhasil diubah menjadi {$newStatus}.";
        return back()->with('success', $message);
    }

    // Mengganti password pegawai
    public function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', "Password untuk akun {$user->name} berhasil diganti.");
    }
}
