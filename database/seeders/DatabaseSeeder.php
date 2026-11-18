<?php

namespace Database\Seeders;

use App\Models\Instansi;
use App\Models\Klasifikasi;
use App\Models\Role;
use App\Models\UnitKerja;
use Illuminate\Database\Seeder;

/**
 * Data master untuk instalasi baru. Akun tidak dibuat di sini: administrator
 * pertama dibuat dengan `php artisan sisurat:buat-admin` agar tidak ada sandi
 * bawaan yang diketahui orang lain.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'staf'] as $n) {
            Role::firstOrCreate(['nama' => $n]);
        }

        UnitKerja::firstOrCreate(['kode' => 'TU'], ['nama' => 'Tata Usaha']);
        UnitKerja::firstOrCreate(['kode' => 'UMPEG'], ['nama' => 'Subbagian Umum dan Kepegawaian']);
        UnitKerja::firstOrCreate(['kode' => 'SEKRETARIAT'], ['nama' => 'Sekretariat']);

        foreach ([['400.7', 'Kesehatan'], ['005', 'Undangan'], ['800', 'Kepegawaian'], ['000', 'Umum']] as [$k, $u]) {
            Klasifikasi::firstOrCreate(['kode' => $k], ['uraian' => $u]);
        }

        $instansi = [
            'Puskesmas Ciapus' => 'Jl. Raya Sukamakmur, Kec. Ciomas, Kab. Bogor',
            'Kecamatan Ciomas' => 'Jl. Raya Ciomas No. 1, Kab. Bogor',
            'Dinas Kesehatan Kab. Bogor' => 'Jl. Tegar Beriman, Cibinong, Kab. Bogor',
            'Puskesmas Sukamakmur' => 'Jl. Raya Sukamakmur No. 12, Kab. Bogor',
        ];
        foreach ($instansi as $nama => $alamat) {
            Instansi::firstOrCreate(['nama' => $nama], ['alamat' => $alamat]);
        }
    }
}
