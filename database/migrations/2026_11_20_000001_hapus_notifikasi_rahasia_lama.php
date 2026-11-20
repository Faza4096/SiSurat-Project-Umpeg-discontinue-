<?php

use App\Services\PembersihNotifikasiRahasia;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(PembersihNotifikasiRahasia::class)->jalankan();
    }

    /** Data yang sudah dihapus tidak dikembalikan. */
    public function down(): void
    {
        //
    }
};
