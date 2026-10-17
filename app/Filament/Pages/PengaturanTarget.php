<?php

namespace App\Filament\Pages;

use App\Models\BaselinePelanggaran;
use App\Models\Pengaturan;
use App\Models\TahunAjaran;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/** Seluruh target dan patokan yang dipakai rekap dan Monev, diatur admin. */
class PengaturanTarget extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Target & Patokan';

    protected static ?string $title = 'Pengaturan Target & Patokan';

    protected static ?string $slug = 'pengaturan';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public function mount(): void
    {
        $nilai = Pengaturan::semua();
        $ta = TahunAjaran::aktif();
        if ($ta) {
            foreach ([1, 2] as $s) {
                $nilai["baseline_$s"] = BaselinePelanggaran::where('tahun_ajaran_id', $ta->id)->where('semester', $s)->value('jumlah');
            }
        }
        $this->form->fill($nilai);
    }

    public function form(Schema $schema): Schema
    {
        $persen = fn (string $kunci, string $label, ?string $bantuan = null) => TextInput::make($kunci)->label($label)
            ->numeric()->minValue(0)->maxValue(100)->suffix('%')->required()->helperText($bantuan);

        $ta = TahunAjaran::aktif();

        return $schema->statePath('data')->components([
            Section::make('Target capaian program')->description('Ditampilkan di Monev sebagai pembanding hasil.')->columns(3)->schema([
                $persen('target_murid_aktif', 'Murid aktif memanfaatkan Teras Baca'),
                $persen('target_guru_aktif', 'Guru aktif memanfaatkan Teras Baca'),
                $persen('target_keberhasilan_pembinaan', 'Keberhasilan pembinaan', 'Murid binaan berstatus Berubah positif / Konsisten positif.'),
                $persen('target_penurunan_pelanggaran', 'Penurunan pelanggaran per semester'),
                $persen('target_budaya_membaca', 'Rata-rata Budaya Membaca'),
                $persen('target_kemampuan_literasi', 'Rata-rata Kemampuan Literasi'),
            ]),
            Section::make('Patokan perhitungan')->columns(3)->schema([
                $persen('batas_murid_aktif', 'Batas murid aktif', 'Kehadiran membaca minimal agar murid dihitung aktif.'),
                $persen('batas_guru_aktif', 'Batas guru aktif', 'Persentase hari mengisi kegiatan harian agar guru dihitung aktif.'),
                $persen('batas_centang_cetak', 'Batas ✓ saat dicetak', 'Pada laporan cetak, angka ≥ batas ini ditampilkan sebagai ✓.'),
                TextInput::make('batas_ubah_hari')->label('Batas guru mengubah catatan')->numeric()->minValue(0)->maxValue(60)->suffix('hari ke belakang')->required(),
            ]),
            Section::make('Skala kategori')->description('Sangat Baik ≥ batas pertama; Baik ≥ batas kedua; Cukup ≥ batas ketiga; di bawahnya Perlu Bimbingan.')->columns(3)->schema([
                $persen('batas_sangat_baik', 'Sangat Baik mulai'),
                $persen('batas_baik', 'Baik mulai'),
                $persen('batas_cukup', 'Cukup mulai'),
            ]),
            Section::make('Baseline jumlah pelanggaran')
                ->description($ta ? "Jumlah pelanggaran sebelum TEBAK AKSI sebagai pembanding penurunan, tahun ajaran {$ta->nama}." : 'Atur tahun ajaran aktif terlebih dahulu.')
                ->columns(2)->visible((bool) $ta)->schema([
                    TextInput::make('baseline_1')->label('Semester Ganjil')->numeric()->minValue(0)->suffix('kasus'),
                    TextInput::make('baseline_2')->label('Semester Genap')->numeric()->minValue(0)->suffix('kasus'),
                ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('simpan')
                ->footer([Actions::make([Action::make('simpan')->label('Simpan pengaturan')->submit('simpan')])]),
        ]);
    }

    public function simpan(): void
    {
        $data = $this->form->getState();
        $ta = TahunAjaran::aktif();

        foreach ([1, 2] as $s) {
            $jumlah = $data["baseline_$s"] ?? null;
            unset($data["baseline_$s"]);
            if ($ta && filled($jumlah)) {
                BaselinePelanggaran::updateOrCreate(['tahun_ajaran_id' => $ta->id, 'semester' => $s], ['jumlah' => (int) $jumlah]);
            } elseif ($ta) {
                BaselinePelanggaran::where('tahun_ajaran_id', $ta->id)->where('semester', $s)->delete();
            }
        }

        Pengaturan::simpan(array_intersect_key($data, Pengaturan::BAWAAN));

        Notification::make()->success()->title('Pengaturan disimpan')->send();
    }
}
