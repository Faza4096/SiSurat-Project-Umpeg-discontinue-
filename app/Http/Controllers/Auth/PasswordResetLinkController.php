<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /** Satu jawaban untuk semua hasil agar tidak bisa dipakai memetakan akun. */
    private const PESAN = 'Jika email tersebut terdaftar, tautan pengaturan kata sandi telah dikirim.';

    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Status hasil tidak dibedakan pada respons: perbedaan pesan membocorkan
        // email mana yang terdaftar sebagai akun pegawai.
        Password::sendResetLink($request->only('email'));

        return back()->with('status', self::PESAN);
    }
}
