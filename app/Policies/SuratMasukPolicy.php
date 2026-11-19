<?php

namespace App\Policies;

use App\Models\SuratMasuk;
use App\Models\User;

class SuratMasukPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SuratMasuk $surat): bool
    {
        return $surat->bolehDilihatOleh($user);
    }

    public function create(User $user): bool
    {
        return $user->punyaRole('staf', 'admin');
    }

    public function update(User $user, SuratMasuk $surat): bool
    {
        return $user->punyaRole('staf', 'admin') && $surat->status !== 'diarsipkan';
    }

    public function delete(User $user, SuratMasuk $surat): bool
    {
        return $user->punyaRole('staf', 'admin') && $surat->bisaDihapus();
    }

    /** ACC & disposisi oleh staf, atau administrator. */
    public function acc(User $user, SuratMasuk $surat): bool
    {
        return $user->punyaRole('staf', 'admin');
    }

    /**
     * Khusus administrator: disposisi, lampiran, dan berkasnya ikut terhapus
     * sehingga tidak dapat dibatalkan.
     */
    public function hapusPermanen(User $user, SuratMasuk $surat): bool
    {
        return $user->punyaRole('admin');
    }

    public function arsipkan(User $user, SuratMasuk $surat): bool
    {
        return $user->punyaRole('staf', 'admin') && $surat->bisaDiarsipkan();
    }

    public function kelolaLampiran(User $user, SuratMasuk $surat): bool
    {
        return $user->punyaRole('staf', 'admin');
    }
}
