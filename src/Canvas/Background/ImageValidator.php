<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Canvas\Background;

use finfo;

final readonly class ImageValidator
{
    public static function isValidImage(string $filePath): bool
    {
        if (! is_file($filePath) || ! is_readable($filePath)) {
            return false;
        }

        $mime = new finfo(FILEINFO_MIME_TYPE)->file($filePath);

        return in_array($mime, ['image/png', 'image/jpeg'], true);
    }
}
