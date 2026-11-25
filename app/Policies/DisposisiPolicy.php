<?php

namespace App\Policies;

use App\Models\Disposisi;
use App\Models\User;

class DisposisiPolicy
{
    /** Surat induk terlihat, atau disposisi memang ditujukan ke unit pengguna. */
    public function view(User $user, Disposisi $disposisi): bool
    {
        if ($disposisi->bolehDilihatOleh($user)) {
            return true;
        }

        $surat = $disposisi->suratMasuk ?? $disposisi->suratKeluar;

        return $surat?->bolehDilihatOleh($user) ?? false;
    }

    /** Peran yang melihat seluruh surat selalu boleh mengunduh kartu. */
    public function unduhKartu(User $user, Disposisi $disposisi): bool
    {
        return $disposisi->bolehDilihatOleh($user);
    }

    /** Hanya pimpinan yang memberikan disposisi. */
    public function create(User $user): bool
    {
        return $user->bolehBeriDisposisi();
    }

    /** Disposisi dibatalkan oleh Pimpinan; administrator untuk pemeliharaan data. */
    public function delete(User $user, Disposisi $disposisi): bool
    {
        return $user->punyaRole('pimpinan', 'admin');
    }
}
