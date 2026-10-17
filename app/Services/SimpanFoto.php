<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

/** Menyimpan foto unggahan setelah dikecilkan agar hemat ruang hosting. */
class SimpanFoto
{
    public const MAKS_KB = 8192;

    public static function simpan(UploadedFile $file, string $folder): string
    {
        $path = trim($folder, '/').'/'.now()->format('Y/m').'/'.Str::uuid().'.jpg';

        $gambar = Image::decode($file->getRealPath())->scaleDown(1600, 1600);
        Storage::disk('public')->put($path, (string) $gambar->encodeUsingFileExtension('jpg', quality: 80));

        return $path;
    }
}
