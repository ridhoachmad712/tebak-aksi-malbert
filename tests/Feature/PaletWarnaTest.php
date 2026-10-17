<?php

namespace Tests\Feature;

use App\Filament\Pages\TampilanSitus;
use App\Models\Pengaturan;
use App\Models\User;
use App\Support\PaletWarna;
use Database\Seeders\DataAwalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaletWarnaTest extends TestCase
{
    use RefreshDatabase;

    /** Rasio kontras WCAG 2.x antara dua warna heksadesimal. */
    protected function kontras(string $a, string $b): float
    {
        $luminans = function (string $hex): float {
            $rgb = array_map(fn ($c) => hexdec($c) / 255, str_split(ltrim($hex, '#'), 2));
            $lin = array_map(fn ($c) => $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4, $rgb);

            return 0.2126 * $lin[0] + 0.7152 * $lin[1] + 0.0722 * $lin[2];
        };
        [$terang, $gelap] = [max($luminans($a), $luminans($b)), min($luminans($a), $luminans($b))];

        return ($terang + 0.05) / ($gelap + 0.05);
    }

    public function test_setiap_palet_memenuhi_kontras_baca(): void
    {
        foreach (PaletWarna::DAFTAR as $kunci => $palet) {
            // Tulisan putih di tombol utama dan centang
            $this->assertGreaterThanOrEqual(4.5, $this->kontras('#ffffff', $palet['aksen']['700']), "$kunci: tombol utama");
            $this->assertGreaterThanOrEqual(4.5, $this->kontras('#ffffff', $palet['pendamping']['700']), "$kunci: centang literasi");
            // Tulisan aksen di latar putih dan krem (menu aktif, tautan)
            $this->assertGreaterThanOrEqual(4.5, $this->kontras('#fffaf3', $palet['aksen']['700']), "$kunci: tautan di latar krem");
            $this->assertGreaterThanOrEqual(4.5, $this->kontras('#fff7ed', $palet['aksen']['800']), "$kunci: tulisan di latar aksen muda");
        }
    }

    public function test_admin_memilih_palet_dan_halaman_memakainya(): void
    {
        $this->seed(DataAwalSeeder::class);
        $this->get('/')->assertSee('--aksen-700:#c2410c', false);

        $this->actingAs(User::where('peran', 'admin')->first());
        Livewire::test(TampilanSitus::class)
            ->set('data.tema_warna', 'biru')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertSame('biru', Pengaturan::ambil('tema_warna'));
        $this->get('/')->assertSee('--aksen-700:#1d4ed8', false);
        auth()->logout();
        $this->get('/masuk')->assertSee('--aksen-700:#1d4ed8', false);
    }

    public function test_palet_tidak_dikenal_kembali_ke_bawaan(): void
    {
        Pengaturan::simpan(['tema_warna' => 'ungu-neon']);

        $this->assertSame('Jingga', PaletWarna::aktif()['label']);
    }
}
