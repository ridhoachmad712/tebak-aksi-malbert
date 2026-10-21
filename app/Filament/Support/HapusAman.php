<?php

namespace App\Filament\Support;

use App\Models\JenisKarya;
use App\Models\JenisKegiatan;
use App\Models\JenisPelanggaran;
use App\Models\KaryaLiterasi;
use App\Models\KasusPembinaan;
use App\Models\Kelas;
use App\Models\Murid;
use App\Models\RiwayatPengisian;
use App\Models\TahunAjaran;
use App\Models\TindakanPembinaan;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Penghapusan yang menjaga riwayat: data yang sudah dipakai catatan guru tidak boleh dihapus.
 * Database juga menolaknya (restrictOnDelete); kelas ini memberi penjelasan kepada admin.
 */
class HapusAman
{
    public static function alasan(Model $record): ?string
    {
        return match (true) {
            $record instanceof TahunAjaran => match (true) {
                $record->kelas()->exists() => 'Tahun ajaran ini masih memiliki kelas. Hapus atau pindahkan kelasnya terlebih dahulu.',
                self::adaBaris('rekap_historis_bulanan', 'tahun_ajaran_id', $record->id) => 'Tahun ajaran ini memiliki rekap historis bulanan (capaian sekolah sebelum memakai aplikasi), sehingga disimpan sebagai riwayat.',
                default => null,
            },
            $record instanceof Kelas => match (true) {
                $record->kegiatanHarian()->exists() || KasusPembinaan::where('kelas_id', $record->id)->exists() => 'Kelas ini sudah memiliki catatan kegiatan atau kasus pembinaan, sehingga disimpan sebagai riwayat.',
                self::adaBaris('rencana_rekonsiliasi_kelas', 'kelas_id', $record->id) => 'Kelas ini masih tercatat di rencana rekonsiliasi data, sehingga belum dapat dihapus.',
                default => null,
            },
            $record instanceof Murid => DB::table('kegiatan_harian_murid')->where('murid_id', $record->id)->exists()
                || $record->kasusPembinaan()->exists()
                || DB::table('karya_murid')->where('murid_id', $record->id)->exists()
                ? 'Murid ini sudah memiliki catatan. Untuk murid yang pindah atau lulus, gunakan Nonaktifkan.' : null,
            $record instanceof User => $record->id === auth()->id()
                ? 'Anda tidak dapat menghapus akun sendiri.'
                : (RiwayatPengisian::where('user_id', $record->id)->exists() || KaryaLiterasi::where('guru_pemilik_id', $record->id)->exists()
                    ? 'Pengguna ini sudah mengisi catatan. Ubah statusnya menjadi Nonaktif agar tidak bisa masuk.' : null),
            $record instanceof JenisPelanggaran => self::dipakai(KasusPembinaan::where('jenis_pelanggaran_id', $record->id)->count(), 'kasus'),
            $record instanceof TindakanPembinaan => self::dipakai(KasusPembinaan::where('tindakan_pembinaan_id', $record->id)->count(), 'kasus'),
            $record instanceof JenisKarya => self::dipakai(KaryaLiterasi::where('jenis_karya_id', $record->id)->count(), 'karya'),
            $record instanceof JenisKegiatan => self::dipakai(DB::table('kegiatan_harian_jenis')->where('jenis_kegiatan_id', $record->id)->count(), 'catatan harian'),
            default => null,
        };
    }

    /** Tabel tambahan yang mungkin belum ada di semua server diperiksa dengan aman. */
    protected static function adaBaris(string $tabel, string $kolom, int $id): bool
    {
        return Schema::hasTable($tabel) && DB::table($tabel)->where($kolom, $id)->exists();
    }

    protected static function dipakai(int $jumlah, string $apa): ?string
    {
        return $jumlah ? "Sudah dipakai di {$jumlah} {$apa}. Matikan \"Aktif\" agar tidak muncul lagi di pilihan guru." : null;
    }

    public static function aksi(): DeleteAction
    {
        return DeleteAction::make()->before(function (DeleteAction $action, Model $record) {
            if ($alasan = static::alasan($record)) {
                Notification::make()->warning()->title('Tidak dapat dihapus')->body($alasan)->persistent()->send();
                $action->cancel();
            }
        });
    }

    public static function aksiMassal(): DeleteBulkAction
    {
        return DeleteBulkAction::make()->action(function (Collection $records) {
            [$ditahan, $boleh] = $records->partition(fn (Model $r) => static::alasan($r) !== null);
            $boleh->each->delete();

            if ($ditahan->isNotEmpty()) {
                Notification::make()->warning()->persistent()
                    ->title($boleh->count().' dihapus, '.$ditahan->count().' tidak dapat dihapus')
                    ->body('Data yang sudah memiliki catatan disimpan sebagai riwayat.')->send();
            } else {
                Notification::make()->success()->title($boleh->count().' data dihapus')->send();
            }
        });
    }
}
