<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create(['must_change_password' => true]);

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'Rahasia2026',
                'password_confirmation' => 'Rahasia2026',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            // Reset lewat email sudah membuktikan kepemilikan akun: penanda
            // wajib-ganti-sandi dibersihkan agar tidak diminta dua kali.
            $user->refresh();
            $this->assertFalse($user->must_change_password);
            $this->assertNotNull($user->password_diubah_pada);

            return true;
        });
    }

    public function test_reset_lewat_email_memutus_sesi_lain(): void
    {
        Notification::fake();
        config(['session.driver' => 'database']);

        $user = User::factory()->create(['must_change_password' => true]);

        DB::table('sessions')->insert([
            'id' => 'sesi-perangkat-lain',
            'user_id' => $user->id,
            'ip_address' => '10.0.0.9',
            'user_agent' => 'peramban uji',
            'payload' => base64_encode('uji'),
            'last_activity' => now()->timestamp,
        ]);

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'Rahasia2026',
                'password_confirmation' => 'Rahasia2026',
            ])
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            $this->assertSame(0, DB::table('sessions')->where('user_id', $user->id)->count());
            $this->assertFalse($user->fresh()->must_change_password);

            return true;
        });
    }

    public function test_tanggapan_tidak_membedakan_email_terdaftar(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $pesan = 'Jika email tersebut terdaftar, tautan pengaturan kata sandi telah dikirim.';

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', $pesan);

        $this->post('/forgot-password', ['email' => 'tidak-terdaftar@example.test'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', $pesan);
    }

    public function test_permintaan_tautan_dibatasi_throttle(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        for ($i = 0; $i < 6; $i++) {
            $this->post('/forgot-password', ['email' => $user->email])->assertRedirect();
        }

        $this->post('/forgot-password', ['email' => $user->email])->assertStatus(429);
    }
}
