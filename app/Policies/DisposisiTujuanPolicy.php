<?php

namespace App\Policies;

use App\Models\DisposisiTujuan;
use App\Models\User;

class DisposisiTujuanPolicy
{
    /**
     * Unit tujuan selalu boleh melihat tindak lanjutnya sendiri (termasuk
     * bukti yang ia unggah), selain itu mengikuti akses surat induk.
     */
    public function view(User $user, DisposisiTujuan $tujuan): bool
    {
        if ($tujuan->bolehDiubahOleh($user)) {
            return true;
        }

        $disposisi = $tujuan->disposisi;
        $surat = $disposisi?->suratMasuk ?? $disposisi?->suratKeluar;

        return $surat?->bolehDilihatOleh($user) ?? false;
    }

    /**
     * Staf/Umpeg mengubah tujuan unitnya sendiri; Petugas TU boleh memperbarui
     * unit mana pun untuk pemantauan tindak lanjut.
     */
    public function ubah(User $user, DisposisiTujuan $tujuan): bool
    {
        return $user->punyaRole('petugas_tu') || $tujuan->bolehDiubahOleh($user);
    }
}
