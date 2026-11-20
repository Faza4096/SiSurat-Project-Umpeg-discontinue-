<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Disposisi extends Model
{
    use SoftDeletes;

    protected $table = 'disposisi';

    protected $fillable = [
        'surat_masuk_id', 'surat_keluar_id', 'dibuat_oleh', 'indeks', 'tgl_penyelesaian',
        'instruksi', 'tgl_disposisi', 'kartu_path', 'kartu_nama', 'kartu_dibuat_pada',
    ];

    protected $casts = [
        'tgl_penyelesaian' => 'date',
        'tgl_disposisi' => 'datetime',
        'kartu_dibuat_pada' => 'datetime',
    ];

    public function suratMasuk()
    {
        return $this->belongsTo(SuratMasuk::class);
    }

    public function suratKeluar()
    {
        return $this->belongsTo(SuratKeluar::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function tujuan()
    {
        return $this->hasMany(DisposisiTujuan::class);
    }

    /** Peran yang tetap boleh menyentuh surat rahasia. */
    public const PERAN_OPERATOR = ['admin', 'staf'];

    /** Surat induk disposisi bertanda rahasia. */
    public function isRahasia(): bool
    {
        return $this->suratMasuk?->isRahasia()
            || $this->suratKeluar?->isRahasia()
            || false;
    }

    /**
     * Staf dan admin melihat semua disposisi; akun lain hanya disposisi yang
     * ditujukan ke unitnya dan surat induknya bukan rahasia.
     */
    public function bolehDilihatOleh(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->punyaRole('admin', 'staf')) {
            return true;
        }

        if ($this->isRahasia()) {
            return false;
        }

        return $user->unit_id !== null
            && $this->tujuan()->where('unit_id', $user->unit_id)->exists();
    }

    /**
     * Penerima notifikasi disposisi: pengguna unit tujuan, tetapi untuk surat
     * rahasia hanya staf/administrator yang boleh menerimanya.
     */
    public function penerimaNotifikasi(?int $unitId = null): \Illuminate\Database\Eloquent\Collection
    {
        $unit = $unitId === null ? $this->tujuan()->pluck('unit_id') : collect([$unitId]);
        $penerima = User::whereIn('unit_id', $unit);

        if ($this->isRahasia()) {
            $penerima->whereHas('role', fn ($q) => $q->whereIn('nama', self::PERAN_OPERATOR));
        }

        return $penerima->get();
    }

    /** Hanya disposisi yang surat induknya bukan rahasia. */
    public function scopeBukanRahasia(Builder $query): Builder
    {
        return $query->whereDoesntHave('suratMasuk', fn ($s) => $s->where('sifat', 'rahasia'))
            ->whereDoesntHave('suratKeluar', fn ($s) => $s->where('sifat', 'rahasia'));
    }

    public function sudahLewatBatas(): bool
    {
        return $this->tgl_penyelesaian !== null && $this->tgl_penyelesaian->isPast();
    }

    public function nomorSurat(): string
    {
        return $this->suratMasuk?->no_agenda
            ?? $this->suratKeluar?->no_surat
            ?? ('DIS-' . $this->id);
    }

    public function perihal(): string
    {
        return $this->suratMasuk?->perihal
            ?? $this->suratKeluar?->hal
            ?? '-';
    }

    public function tanggalSurat()
    {
        return $this->suratMasuk?->tgl_surat ?? $this->suratKeluar?->tgl_surat;
    }

    public function nomorSuratAsli(): string
    {
        return $this->suratMasuk?->no_surat ?? $this->suratKeluar?->no_surat ?? '-';
    }

    /** "Dari" pada kartu: pengirim surat masuk, atau unit pembuat surat keluar. */
    public function asal(): string
    {
        if ($this->suratMasuk) {
            return $this->suratMasuk->instansi?->nama ?? '-';
        }

        return $this->suratKeluar?->unit?->nama
            ?? $this->suratKeluar?->unit?->kode
            ?? '-';
    }

    public function tautanSurat(): string
    {
        if ($this->suratMasuk) {
            return route('surat-masuk.show', $this->suratMasuk);
        }

        if ($this->suratKeluar) {
            return route('surat-keluar.show', $this->suratKeluar);
        }

        return route('disposisi.index');
    }

    public function sudahAdaKartu(): bool
    {
        return $this->kartu_path !== null;
    }
}
