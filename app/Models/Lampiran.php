<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lampiran extends Model
{
    public const JENIS = [
        'scan' => 'Scan Surat',
        'final' => 'Surat Final',
        'bukti' => 'Bukti Tindak Lanjut',
        'lainnya' => 'Lainnya',
    ];

    protected $table = 'lampiran';

    protected $fillable = [
        'surat_type', 'surat_id', 'nama_file', 'path', 'jenis', 'diunggah_oleh',
    ];

    public function surat()
    {
        return $this->morphTo();
    }

    public function pengunggah()
    {
        return $this->belongsTo(User::class, 'diunggah_oleh');
    }
}
