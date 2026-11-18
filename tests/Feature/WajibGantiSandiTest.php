<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WajibGantiSandiTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_dengan_sandi_default_diarahkan_mengganti_sandi(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('password.wajib.edit'));

        $this->actingAs($user)->get(route('password.wajib.edit'))->assertOk()->assertSee('Ganti Kata Sandi Anda');
    }

    public function test_sandi_baru_harus_kuat_dan_berbeda(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        $this->actingAs($user)->put(route('password.wajib.update'), [
            'password_lama' => 'password',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('password');

        $this->assertTrue($user->fresh()->must_change_password);
    }

    public function test_setelah_mengganti_sandi_pengguna_bisa_mengakses_aplikasi(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        $this->actingAs($user)->put(route('password.wajib.update'), [
            'password_lama' => 'password',
            'password' => 'SandiBaru2026',
            'password_confirmation' => 'SandiBaru2026',
        ])->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check('SandiBaru2026', $user->password));

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }
}
