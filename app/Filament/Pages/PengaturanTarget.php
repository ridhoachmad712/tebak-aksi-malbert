<?php

namespace App\Filament\Pages;

use App\Models\BaselinePelanggaran;
use App\Models\Pengaturan;
use App\Models\TahunAjaran;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
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
        // Tanggal mulai ubah yang belum pernah diatur ditampilkan sesuai perhitungan yang sedang berlaku
        $nilai['ubah_mulai_tanggal'] = Pengaturan::ubahMulai()->toDateString();
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
            ]),
            Section::make('Target 5 aspek inovasi')
                ->description('Ditampilkan di Dashboard kepala sekolah dan Monev, dihitung per jumlah murid seperti laporan hasil inovasi. Kepatuhan Perilaku memakai target Keberhasilan pembinaan, dan murid aktif pada Ekosistem Literat memakai target Murid aktif di atas.')
                ->columns(2)->schema([
                    $persen('target_aspek_budaya', 'Budaya Membaca', 'Murid yang membaca konsisten dan mengisi jurnal minimal sebesar batas murid aktif.'),
                    $persen('target_aspek_literasi', 'Kemampuan Literasi', 'Murid yang memahami, merangkum, atau menceritakan kembali isi bacaan.'),
                    $persen('target_penurunan_bulanan', 'Penguatan Karakter: penurunan per bulan', 'Penurunan jumlah murid yang melanggar dibanding bulan sebelumnya.'),
                    $persen('target_murid_berkarya', 'Ekosistem Literat: murid berkarya', 'Murid yang menghasilkan minimal 1 karya dalam sebulan. 100% berarti setiap murid.'),
                ]),
            Section::make('Patokan perhitungan')->columns(3)->schema([
                $persen('batas_murid_aktif', 'Batas murid aktif', 'Kehadiran membaca minimal agar murid dihitung aktif.'),
                $persen('batas_guru_aktif', 'Batas guru aktif', 'Persentase hari mengisi kegiatan harian agar guru dihitung aktif.'),
                $persen('batas_centang_cetak', 'Batas ✓ saat dicetak', 'Pada laporan cetak, angka ≥ batas ini ditampilkan sebagai ✓.'),
                DatePicker::make('ubah_mulai_tanggal')->label('Guru boleh mengubah catatan mulai tanggal')
                    ->native(false)->displayFormat('d F Y')->locale('id')->maxDate(today())->required()
                    ->helperText('Catatan sejak tanggal ini sampai hari ini dapat diisi atau diubah guru. Majukan tanggalnya setelah catatan lama lengkap.'),
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
