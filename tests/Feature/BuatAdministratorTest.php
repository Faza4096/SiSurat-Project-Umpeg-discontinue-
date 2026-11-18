<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BuatAdministratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_perintah_membuat_administrator_pertama(): void
    {
        $this->artisan('sisurat:buat-admin', [
            'email' => 'kepala@bogorkab.go.id',
            '--nama' => 'Kepala Dinas',
            '--sandi' => 'Rahasia2026',
        ])->assertSuccessful();

        $user = User::where('email', 'kepala@bogorkab.go.id')->firstOrFail();

        $this->assertSame('admin', $user->role->nama);
        $this->assertTrue($user->aktif);
        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check('Rahasia2026', $user->password));
    }

    public function test_surel_yang_sudah_terpakai_ditolak(): void
    {
        User::factory()->admin()->create(['email' => 'kepala@bogorkab.go.id']);

        $this->artisan('sisurat:buat-admin', [
            'email' => 'kepala@bogorkab.go.id',
            '--nama' => 'Kepala Dinas',
            '--sandi' => 'Rahasia2026',
        ])->assertFailed();

        $this->assertSame(1, User::where('email', 'kepala@bogorkab.go.id')->count());
    }

    public function test_sandi_lemah_ditolak(): void
    {
        $this->artisan('sisurat:buat-admin', [
            'email' => 'kepala@bogorkab.go.id',
            '--nama' => 'Kepala Dinas',
            '--sandi' => 'rahasia',
        ])->assertFailed();

        $this->assertNull(User::where('email', 'kepala@bogorkab.go.id')->first());
    }
}
