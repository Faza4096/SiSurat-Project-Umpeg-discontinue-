<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class UnitKerja extends Model {
    protected $table = 'unit_kerja';
    protected $fillable = ['kode', 'nama'];

    public function pengguna() { return $this->hasMany(User::class, 'unit_id'); }
    public function suratKeluar() { return $this->hasMany(SuratKeluar::class, 'unit_id'); }
    public function tujuanDisposisi() { return $this->hasMany(DisposisiTujuan::class, 'unit_id'); }
}
