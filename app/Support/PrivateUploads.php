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
        try {
            return $file->storeAs(self::directory($category), basename($filename), self::diskName()) !== false;
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }
    }

    public static function delete(string $category, ?string $filename): bool
    {
        if (! $filename) {
            return true;
        }

        try {
            return self::disk()->delete(self::path($category, $filename));
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }
    }

    public static function exists(string $category, ?string $filename): bool
    {
        if ($filename === null) {
            return false;
        }

        try {
            return self::disk()->exists(self::path($category, $filename));
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }
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
