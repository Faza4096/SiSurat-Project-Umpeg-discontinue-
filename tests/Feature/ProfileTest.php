<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/profile')->assertOk();
    }

    public function test_nama_profil_dapat_diubah_tanpa_kata_sandi(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', ['name' => 'Test User', 'email' => $user->email])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertSame('Test User', $user->fresh()->name);
    }

    public function test_perubahan_email_wajib_konfirmasi_kata_sandi(): void
    {
        $user = User::factory()->create();
        $lama = $user->email;

        $this->actingAs($user)
            ->patch('/profile', ['name' => $user->name, 'email' => 'baru@example.com'])
            ->assertSessionHasErrors('current_password');

        $this->assertSame($lama, $user->fresh()->email);
    }

    public function test_perubahan_email_berhasil_dengan_kata_sandi_benar(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'baru@example.com',
                'current_password' => 'password',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();
        $this->assertSame('Test User', $user->name);
        $this->assertSame('baru@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_perubahan_email_ditolak_bila_kata_sandi_salah(): void
    {
        $user = User::factory()->create();
        $lama = $user->email;

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => 'baru@example.com',
                'current_password' => 'sandi-salah',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertSame($lama, $user->fresh()->email);
    }

    public function test_pengguna_tidak_dapat_menghapus_akunnya_sendiri(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete('/profile', ['password' => 'password'])
            ->assertStatus(405);

        $this->assertNotNull($user->fresh());
        $this->assertAuthenticatedAs($user);
    }
}
