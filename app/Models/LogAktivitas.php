<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LogAktivitas extends Model {
    protected $table = 'log_aktivitas';
    public $timestamps = false;
    protected $fillable = ['pengguna_id', 'aksi', 'keterangan', 'waktu'];
    protected $casts = ['waktu' => 'datetime'];
    public function pengguna() { return $this->belongsTo(User::class, 'pengguna_id'); }
    public static function catat(string $aksi, ?string $ket = null): void {
        static::create(['pengguna_id' => auth()->id(), 'aksi' => $aksi, 'keterangan' => $ket, 'waktu' => now()]);
    }
}
