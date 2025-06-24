<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon; // Ditambahkan untuk pengecekan waktu

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     * PERBAIKAN: Validasi token sekarang juga memeriksa waktu kedaluwarsa.
     */
    public function create(Request $request): View|RedirectResponse
    {
        // Ambil data token dari database berdasarkan email
        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        // Ambil waktu kedaluwarsa dari konfigurasi (default: 60 menit)
        $expires = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        // Jika token tidak ada, tidak cocok, atau sudah lewat dari waktu kedaluwarsa, redirect dengan error.
        if (
            !$tokenData ||
            !Hash::check($request->route('token'), $tokenData->token) ||
            Carbon::parse($tokenData->created_at)->addMinutes($expires)->isPast()
        ) {
            return redirect()->route('password.request')
                ->withErrors(['email' => trans('passwords.token')]);
        }
        
        // Jika valid, tampilkan halaman reset password
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Attempt to reset the password...
        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was reset, redirect to the login page with a success message.
        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        // If the password reset failed, redirect back with the error message.
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
