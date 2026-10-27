<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->merge(['phone_wa' => $this->normalizePhone($request->input('phone_wa'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone_wa' => [
                $user->isAdmin() ? 'nullable' : 'required',
                'regex:/^628[0-9]{8,12}$/',
                Rule::unique('users', 'phone_wa')->ignore($user->id),
            ],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'phone_wa.required' => 'Nomor WhatsApp wajib diisi.',
            'phone_wa.regex' => 'Nomor WhatsApp tidak valid. Contoh: 081234567890.',
            'phone_wa.unique' => 'Nomor WhatsApp sudah dipakai akun lain.',
            'current_password.required_with' => 'Isi sandi lama untuk mengganti sandi.',
            'current_password.current_password' => 'Sandi lama salah.',
            'password.min' => 'Sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi sandi baru tidak sama.',
        ]);

        $user->name = $data['name'];
        $user->phone_wa = $data['phone_wa'] ?: null;
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    private function normalizePhone(?string $phone): ?string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone ?? '');
        $phone = ltrim($phone, '+');

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return $phone === '' ? null : $phone;
    }
}
