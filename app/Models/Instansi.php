<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Instansi extends Model {
    protected $table = 'instansi';
    protected $fillable = ['nama', 'alamat', 'email', 'telepon'];

    public function suratMasuk() { return $this->hasMany(SuratMasuk::class); }
    public function suratKeluar() { return $this->hasMany(SuratKeluar::class); }
}
