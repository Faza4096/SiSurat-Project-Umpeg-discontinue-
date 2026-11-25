<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Services\FotoProfilService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Email adalah pintu masuk pemulihan akun: mengubahnya wajib dikonfirmasi
        // agar sesi yang dibajak tidak bisa mengambil alih akun secara permanen.
        if ($request->validated('email') !== $user->email) {
            $request->validate([
                'current_password' => ['required', 'current_password'],
            ], [
                'current_password.required' => 'Masukkan kata sandi saat ini untuk mengubah email.',
                'current_password.current_password' => 'Kata sandi saat ini tidak cocok.',
            ]);
        }

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /** Foto profil opsional: pengguna dapat mengunggahnya kapan saja. */
    public function unggahFoto(Request $request, FotoProfilService $pengolahFoto): RedirectResponse
    {
        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'foto.required' => 'Pilih berkas foto yang akan diunggah.',
            'foto.image' => 'Foto harus berupa gambar.',
            'foto.mimes' => 'Format foto tidak didukung. Unggah foto JPG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto tidak boleh melebihi 2 MB.',
        ]);

        $user = $request->user();
        $lama = $user->foto_profil;

        // Diperkecil dulu agar header tidak memuat berkas hingga 2 MB, lalu
        // disimpan di disk privat dan hanya keluar lewat rute yang memeriksa sesi.
        $user->update(['foto_profil' => $pengolahFoto->simpan($request->file('foto'))]);

        if ($lama && $lama !== $user->foto_profil) {
            Storage::delete($lama);
        }

        return Redirect::route('profile.edit')->with('status', 'foto-profil-diperbarui');
    }

    public function hapusFoto(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->foto_profil) {
            Storage::delete($user->foto_profil);
            $user->update(['foto_profil' => null]);
        }

        return Redirect::route('profile.edit')->with('status', 'foto-profil-dihapus');
    }

    /** Menampilkan foto profil kepada pengguna yang sudah masuk. */
    public function foto(User $user)
    {
        abort_unless($user->foto_profil && Storage::exists($user->foto_profil), 404, 'Foto profil tidak ditemukan.');

        return Storage::response($user->foto_profil, null, [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    // Penghapusan akun sendiri dinonaktifkan; akun dikelola administrator agar
    // jejak surat tetap utuh.
}
