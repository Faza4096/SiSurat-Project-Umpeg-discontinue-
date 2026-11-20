<?php

use App\Services\PembersihAntreanRahasia;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(PembersihAntreanRahasia::class)->jalankan();
    }

    /** Data yang sudah dihapus tidak dikembalikan. */
    public function down(): void
    {
        //
    }
};
