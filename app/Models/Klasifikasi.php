<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Klasifikasi extends Model {
    protected $table = 'klasifikasi';
    protected $fillable = ['kode', 'uraian'];

    public function suratMasuk() { return $this->hasMany(SuratMasuk::class); }
    public function suratKeluar() { return $this->hasMany(SuratKeluar::class); }
}
