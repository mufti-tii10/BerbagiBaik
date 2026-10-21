<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // Samakan format nomor: 0812xxx / +62812xxx / 62812xxx -> 62812xxx
        $request->merge(['phone_wa' => $this->normalizePhone($request->input('phone_wa'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone_wa' => ['required', 'regex:/^628[0-9]{8,12}$/', 'unique:users,phone_wa'],
            'role' => ['required', 'in:donor,receiver'], // admin TIDAK boleh dipilih dari form
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'phone_wa.regex' => 'Nomor WhatsApp tidak valid. Contoh: 081234567890.',
            'phone_wa.unique' => 'Nomor WhatsApp sudah terdaftar.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi sandi tidak sama.',
            'password.min' => 'Sandi minimal 8 karakter.',
            'role.in' => 'Pilih peran donatur atau penerima.',
        ]);

        $user = User::create($data);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (Auth::user()->is_blocked) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors(['email' => 'Akun Anda diblokir. Hubungi admin.'])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Email atau sandi salah.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function normalizePhone(?string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone ?? '');
        $phone = ltrim($phone, '+');

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }
}
