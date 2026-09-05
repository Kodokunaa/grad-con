<?php

namespace App\Support;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class PrivateUploads
{
    private const ROOT = 'files/uploads';

    public static function store(UploadedFile $file, string $category, string $filename): bool
    {
        return $file->storeAs(self::directory($category), basename($filename), self::diskName()) !== false;
    }

    public static function delete(string $category, ?string $filename): bool
    {
        if (! $filename) {
            return true;
        }

        return self::disk()->delete(self::path($category, $filename));
    }

    public static function exists(string $category, ?string $filename): bool
    {
        return $filename !== null && self::disk()->exists(self::path($category, $filename));
    }

    public static function diskName(): string
    {
        return (string) config('filesystems.uploads_disk', 'local');
    }

    public static function disk(): FilesystemAdapter
    {
        return Storage::disk(self::diskName());
    }

    public static function path(string $category, string $filename): string
    {
        return self::directory($category).'/'.basename($filename);
    }

    private static function directory(string $category): string
    {
        return self::ROOT.'/'.trim($category, '/');
    }
}
