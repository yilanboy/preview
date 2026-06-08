<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Text;

final class FontValidator
{
    // sfnt version tag (hex) of a TrueType font. OpenType ('OTTO') is excluded.
    private const string VALID_TTF_HEADER = '00010000';

    public static function isValidTtf(string $filePath): bool
    {
        if (! is_file($filePath) || ! is_readable($filePath)) {
            return false;
        }

        if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) !== 'ttf') {
            return false;
        }

        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            return false;
        }

        // Check the file header (prevent fake file extension).
        $header = bin2hex((string) fread($handle, 4));
        fclose($handle);

        return $header === self::VALID_TTF_HEADER;
    }
}
