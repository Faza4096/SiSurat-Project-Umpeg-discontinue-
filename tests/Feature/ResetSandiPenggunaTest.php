<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ResetSandiPenggunaTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_sandi_admin_memutus_sesi_dan_mewajibkan_ganti(): void
    {
        config(['session.driver' => 'database']);

        $admin = User::factory()->admin()->create();
        $staf = User::factory()->staf()->create(['must_change_password' => false]);
        $tokenLama = $staf->remember_token;

        $this->buatSesi($staf, 'sesi-perangkat-lama');

        $this->actingAs($admin)
            ->put(route('pengguna.update', $staf), [
                'name' => $staf->name,
                'email' => $staf->email,
                'role_id' => $staf->role_id,
                'unit_id' => $staf->unit_id,
                'password' => 'SandiBaru2026',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('pengguna.index'));

        $this->assertSame(0, DB::table('sessions')->where('user_id', $staf->id)->count());

        $staf->refresh();
        $this->assertTrue($staf->must_change_password);
        $this->assertNotSame($tokenLama, $staf->remember_token);
        $this->assertTrue(Hash::check('SandiBaru2026', $staf->password));
    }

    public function test_ubah_data_tanpa_sandi_tidak_memutus_sesi(): void
    {
        config(['session.driver' => 'database']);

        $admin = User::factory()->admin()->create();
        $staf = User::factory()->staf()->create(['must_change_password' => false]);
        $tokenLama = $staf->remember_token;

        $this->buatSesi($staf, 'sesi-tetap-aktif');

        $this->actingAs($admin)
            ->put(route('pengguna.update', $staf), [
                'name' => 'Nama Baru',
                'email' => $staf->email,
                'role_id' => $staf->role_id,
                'unit_id' => $staf->unit_id,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('pengguna.index'));

        $this->assertSame(1, DB::table('sessions')->where('user_id', $staf->id)->count());

        $staf->refresh();
        $this->assertSame('Nama Baru', $staf->name);
        $this->assertSame($tokenLama, $staf->remember_token);
        $this->assertFalse($staf->must_change_password);
    }

    private function buatSesi(User $user, string $id): void
    {
        DB::table('sessions')->insert([
            'id' => $id,
            'user_id' => $user->id,
            'ip_address' => '10.0.0.5',
            'user_agent' => 'peramban uji',
            'payload' => base64_encode('uji'),
            'last_activity' => now()->timestamp,
        ]);
    }
}
