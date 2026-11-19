<?php

namespace App\Policies;

use App\Models\SuratKeluar;
use App\Models\User;

class SuratKeluarPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SuratKeluar $surat): bool
    {
        return $surat->bolehDilihatOleh($user);
    }

    public function create(User $user): bool
    {
        return $user->punyaRole('staf', 'admin');
    }

    public function update(User $user, SuratKeluar $surat): bool
    {
        return $user->punyaRole('staf', 'admin') && $surat->bisaDiubah();
    }

    public function delete(User $user, SuratKeluar $surat): bool
    {
        return $user->punyaRole('staf', 'admin') && $surat->bisaDihapus();
    }

    /**
     * Khusus administrator: kartu disposisi, lampiran, dan berkas final ikut
     * terhapus sehingga tidak dapat dibatalkan.
     */
    public function hapusPermanen(User $user, SuratKeluar $surat): bool
    {
        return $user->punyaRole('admin');
    }

    public function ajukan(User $user, SuratKeluar $surat): bool
    {
        return $user->punyaRole('staf', 'admin')
            && in_array($surat->status, ['draf', 'revisi'], true);
    }

    public function setujui(User $user, SuratKeluar $surat): bool
    {
        return $this->acc($user, $surat);
    }

    /** ACC hanya dari status menunggu_ttd. */
    public function acc(User $user, SuratKeluar $surat): bool
    {
        return $user->punyaRole('staf', 'admin') && $surat->status === 'menunggu_ttd';
    }

    public function tolak(User $user, SuratKeluar $surat): bool
    {
        return $user->punyaRole('staf', 'admin') && $surat->status === 'menunggu_ttd';
    }

    public function kirim(User $user, SuratKeluar $surat): bool
    {
        return $user->punyaRole('staf', 'admin') && $surat->status === 'disetujui';
    }

    public function arsipkan(User $user, SuratKeluar $surat): bool
    {
        return $user->punyaRole('staf', 'admin') && $surat->status === 'terkirim';
    }
}
