<?php

namespace App\Filament\Pages;

use App\Models\Foto;
use App\Models\Pengaturan;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Pengaturan isi tampilan situs: identitas sekolah, teks halaman depan, alur sesi, dan menu publik.
 * Warna dan huruf sengaja tidak bisa diubah agar tampilan tetap konsisten dan mudah dibaca (DESIGN.md).
 */
class TampilanSitus extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    protected static string|UnitEnum|null $navigationGroup = 'Halaman Publik';

    protected static ?string $navigationLabel = 'Tampilan Situs';

    protected static ?string $title = 'Tampilan Situs';

    protected static ?string $slug = 'tampilan-situs';

    protected static ?int $navigationSort = -2;

    /** Kunci pengaturan yang dikelola halaman ini. */
    public const KUNCI = [
        'nama_sekolah', 'alamat_sekolah', 'kota_ttd', 'kontak_sekolah', 'logo_sekolah',
        'medsos_instagram', 'medsos_facebook', 'medsos_youtube', 'medsos_whatsapp',
        'beranda_judul', 'beranda_paragraf', 'beranda_tombol1_teks', 'beranda_tombol1_tujuan',
        'beranda_tombol2_teks', 'beranda_tombol2_tujuan', 'beranda_foto_id',
        'beranda_tampil_angka', 'beranda_tampil_alur', 'beranda_tampil_galeri', 'beranda_tampil_jalan_pintas', 'beranda_tampil_berita',
        'alur_judul', 'alur_langkah', 'menu_publik',
    ];

    public ?array $data = [];

    public function mount(): void
    {
        $nilai = [];
        foreach (self::KUNCI as $kunci) {
            $nilai[$kunci] = match (true) {
                in_array($kunci, ['alur_langkah', 'menu_publik'], true) => Pengaturan::daftar($kunci),
                str_starts_with($kunci, 'beranda_tampil_') => Pengaturan::aktif($kunci),
                default => Pengaturan::ambil($kunci) ?: null,
            };
        }
        $this->form->fill($nilai);
    }

    public function form(Schema $schema): Schema
    {
        $tujuan = fn () => Pengaturan::pilihanTujuan();

        return $schema->statePath('data')->components([
            Tabs::make('tampilan')->persistTabInQueryString()->tabs([
                Tab::make('Identitas')->icon(Heroicon::OutlinedBuildingLibrary)->schema([
                    Section::make('Sekolah')->description('Dipakai di header, footer, halaman masuk, dan laporan cetak.')->columns(2)->schema([
                        TextInput::make('nama_sekolah')->required()->maxLength(150)->columnSpanFull(),
                        TextInput::make('alamat_sekolah')->label('Alamat')->maxLength(255),
                        TextInput::make('kota_ttd')->label('Kota (tempat tanda tangan laporan)')->maxLength(60),
                        TextInput::make('kontak_sekolah')->label('Kontak (telepon/email)')->maxLength(150),
                        FileUpload::make('logo_sekolah')->label('Logo sekolah')->image()->disk('public')->directory('pengaturan')->maxSize(2048)
                            ->imageEditor()->helperText('Tampil di header, ikon tab browser, dan kop laporan. Sebaiknya berbentuk persegi.'),
                    ]),
                    Section::make('Media sosial')->description('Tampil di footer. Kosongkan yang tidak dipakai.')->columns(2)->schema([
                        TextInput::make('medsos_instagram')->label('Instagram')->url()->placeholder('https://instagram.com/akun-sekolah'),
                        TextInput::make('medsos_facebook')->label('Facebook')->url()->placeholder('https://facebook.com/akun-sekolah'),
                        TextInput::make('medsos_youtube')->label('YouTube')->url()->placeholder('https://youtube.com/@kanal-sekolah'),
                        TextInput::make('medsos_whatsapp')->label('WhatsApp')->tel()->placeholder('08xxxxxxxxxx')
                            ->helperText('Nomor WhatsApp sekolah untuk dihubungi orang tua.'),
                    ]),
                ]),

                Tab::make('Halaman Depan')->icon(Heroicon::OutlinedHome)->schema([
                    Section::make('Pembuka')->schema([
                        TextInput::make('beranda_judul')->label('Judul')->required()->maxLength(90)
                            ->helperText('Kalimat singkat, sebaiknya tidak lebih dari 10 kata.'),
                        Textarea::make('beranda_paragraf')->label('Paragraf pembuka')->required()->rows(3)->maxLength(400),
                        Grid::make(2)->schema([
                            TextInput::make('beranda_tombol1_teks')->label('Tombol utama: teks')->required()->maxLength(30),
                            Select::make('beranda_tombol1_tujuan')->label('Tombol utama: menuju')->options($tujuan)->required(),
                            TextInput::make('beranda_tombol2_teks')->label('Tombol kedua: teks')->maxLength(30)->helperText('Kosongkan untuk menyembunyikan tombol kedua.'),
                            Select::make('beranda_tombol2_tujuan')->label('Tombol kedua: menuju')->options($tujuan),
                        ]),
                        Select::make('beranda_foto_id')->label('Foto pembuka')
                            ->options(fn () => Foto::where('tampil_galeri', true)->latest()->limit(60)->get()
                                ->mapWithKeys(fn (Foto $f) => [$f->id => '<span style="display:flex;align-items:center;gap:.75rem"><img src="'.e($f->url()).'" style="height:2.5rem;width:3.5rem;object-fit:cover;border-radius:.375rem"> '.e($f->keterangan ?: $f->sumber().', '.$f->created_at->format('d/m/Y')).'</span>']))
                            ->allowHtml()->searchable(false)->placeholder('Tanpa foto: tampilkan arti nama TEBAK AKSI')
                            ->helperText('Pilihan diambil dari foto yang sudah ditampilkan di Galeri. Pilih foto kegiatan yang tidak menonjolkan wajah atau nama murid.'),
                    ]),
                    Section::make('Bagian yang ditampilkan')->description('Sembunyikan bagian yang isinya belum ada.')->columns(2)->schema([
                        Toggle::make('beranda_tampil_angka')->label('Angka bulan ini'),
                        Toggle::make('beranda_tampil_alur')->label('Alur sesi Teras Baca'),
                        Toggle::make('beranda_tampil_galeri')->label('Galeri kegiatan')->helperText('Hanya muncul bila ada foto di Galeri.'),
                        Toggle::make('beranda_tampil_jalan_pintas')->label('Jalan pintas orang tua dan guru'),
                        Toggle::make('beranda_tampil_berita')->label('Berita terbaru')->helperText('Hanya muncul bila ada berita terbit.'),
                    ]),
                ]),

                Tab::make('Alur Sesi')->icon(Heroicon::OutlinedListBullet)->schema([
                    TextInput::make('alur_judul')->label('Judul bagian')->required()->maxLength(80),
                    Repeater::make('alur_langkah')->label('Langkah')->schema([
                        TextInput::make('judul')->required()->maxLength(40),
                        Textarea::make('keterangan')->required()->rows(2)->maxLength(200),
                    ])->columns(2)->minItems(1)->maxItems(6)->reorderable()->addActionLabel('Tambah langkah')
                        ->itemLabel(fn (array $state) => $state['judul'] ?? null)->collapsible()
                        ->helperText('Urutan di sini sama dengan urutan nomor di halaman depan. Paling banyak 6 langkah.'),
                ]),

                Tab::make('Menu')->icon(Heroicon::OutlinedBars3)->schema([
                    Repeater::make('menu_publik')->label('Menu halaman publik')->schema([
                        Select::make('tujuan')->label('Menuju')->options($tujuan)->required(),
                        TextInput::make('label')->label('Tulisan di menu')->required()->maxLength(20)->helperText('Singkat, 1–2 kata.'),
                        Toggle::make('tampil')->label('Tampilkan')->default(true)->inline(false),
                    ])->columns(3)->minItems(1)->maxItems(9)->reorderable()->addActionLabel('Tambah menu')
                        ->itemLabel(fn (array $state) => $state['label'] ?? null)
                        ->helperText('Seret untuk mengubah urutan. Tombol Masuk selalu tampil di ujung kanan. Halaman baru dibuat di menu Halaman & Berita.'),
                ]),
            ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('simpan')
                ->footer([Actions::make([
                    Action::make('simpan')->label('Simpan tampilan')->submit('simpan'),
                    Action::make('lihat')->label('Lihat halaman publik')->color('gray')->url(route('beranda'), shouldOpenInNewTab: true),
                ])]),
        ]);
    }

    public function simpan(): void
    {
        $data = $this->form->getState();

        foreach (['logo_sekolah', 'beranda_foto_id', 'beranda_tombol2_teks', 'beranda_tombol2_tujuan'] as $kunci) {
            $data[$kunci] = $data[$kunci] ?? '';
        }
        $data['menu_publik'] = collect($data['menu_publik'] ?? [])
            ->map(fn ($m) => ['tujuan' => $m['tujuan'], 'label' => $m['label'], 'tampil' => (bool) ($m['tampil'] ?? false)])->all();

        Pengaturan::simpan(array_intersect_key($data, array_flip(self::KUNCI)));

        Notification::make()->success()->title('Tampilan situs disimpan')->send();
    }
}
