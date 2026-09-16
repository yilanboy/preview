<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Text;

use Yilanboy\Preview\Exceptions\RenderFailure;
use Yilanboy\Preview\Text\Enums\Font;

final class FontStorage
{
    private const string DEFAULT_BASE_URL = 'https://cdn.jsdelivr.net/gh/yilanboy/preview@main/fonts';

    private static ?string $storagePath = null;

    private static ?string $baseUrl = null;

    public static function setStoragePath(?string $path): void
    {
        self::$storagePath = $path !== null ? rtrim($path, '/\\') : null;
    }

    public static function getStoragePath(): string
    {
        return self::$storagePath ?? sys_get_temp_dir().'/yilanboy-preview/fonts';
    }

    public static function setBaseUrl(?string $url): void
    {
        self::$baseUrl = $url !== null ? rtrim($url, '/') : null;
    }

    public static function getBaseUrl(): string
    {
        return self::$baseUrl ?? self::DEFAULT_BASE_URL;
    }

    public static function resolve(Font $font): string
    {
        $targetPath = self::getStoragePath().'/'.$font->value;

        if (is_file($targetPath)) {
            return $targetPath;
        }

        return self::download($font);
    }

    public static function download(Font $font): string
    {
        $dir = self::getStoragePath();
        if (! is_dir($dir) && ! @mkdir($dir, 0777, true) && ! is_dir($dir)) {
            throw new RenderFailure("Failed to create font directory: {$dir}");
        }

        $targetPath = $dir.'/'.$font->value;
        $tempPath = $targetPath.'.tmp.'.bin2hex(random_bytes(4)).'.ttf';
        $url = self::getBaseUrl().'/'.$font->value;

        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'follow_location' => 1,
                'header' => "User-Agent: yilanboy-preview\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        set_error_handler(static fn (): bool => true);
        try {
            $fp = fopen($url, 'rb', false, $context);
        } finally {
            restore_error_handler();
        }

        if ($fp === false) {
            throw new RenderFailure("Failed to download font [{$font->value}] from {$url}");
        }

        set_error_handler(static fn (): bool => true);
        try {
            $out = fopen($tempPath, 'wb');
        } finally {
            restore_error_handler();
        }

        if ($out === false) {
            fclose($fp);
            throw new RenderFailure("Failed to open temporary file for writing: {$tempPath}");
        }

        stream_copy_to_stream($fp, $out);
        fclose($fp);
        fclose($out);

        if (! FontValidator::isValidTtf($tempPath)) {
            @unlink($tempPath);
            throw new RenderFailure("Downloaded file for [{$font->value}] from {$url} is not a valid TrueType font");
        }

        if (! @rename($tempPath, $targetPath)) {
            @unlink($tempPath);
            throw new RenderFailure("Failed to save font to: {$targetPath}");
        }

        return $targetPath;
    }

    public static function downloadAll(): void
    {
        foreach (Font::cases() as $font) {
            self::download($font);
        }
    }

    public static function reset(): void
    {
        self::$storagePath = null;
        self::$baseUrl = null;
    }
}
