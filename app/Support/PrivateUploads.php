<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class PrivateUploads
{
    private const ROOT = 'files/uploads';

    public static function store(UploadedFile $file, string $category, string $filename): bool
    {
        return $file->storeAs(self::directory($category), basename($filename), 'local') !== false;
    }

    public static function delete(string $category, ?string $filename): bool
    {
        if (! $filename) {
            return true;
        }

        return Storage::disk('local')->delete(self::path($category, $filename));
    }

    public static function exists(string $category, ?string $filename): bool
    {
        return $filename !== null && Storage::disk('local')->exists(self::path($category, $filename));
    }

    public static function absolutePath(string $category, string $filename): string
    {
        return Storage::disk('local')->path(self::path($category, $filename));
    }

    private static function path(string $category, string $filename): string
    {
        return self::directory($category).'/'.basename($filename);
    }

    private static function directory(string $category): string
    {
        return self::ROOT.'/'.trim($category, '/');
    }
}
