<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_registrasi_publik_tidak_tersedia(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_pendaftaran_publik_ditolak(): void
    {
        $this->post('/register', [
            'name' => 'Penyerang',
            'email' => 'penyerang@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'penyerang@example.com']);
        $this->assertGuest();
    }

    public function test_akun_hanya_bisa_dibuat_administrator(): void
    {
        $this->get('/register')->assertNotFound();

        // Hanya administrator yang boleh menambah pengguna.
        $staf = User::factory()->create();
        $this->actingAs($staf)->get(route('pengguna.create'))->assertForbidden();
    }
}
