<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Canvas\Enums;

use GdImage;

enum Format
{
    case PNG;
    case JPEG;
    case WEBP;

    public function mimeType(): string
    {
        return match ($this) {
            self::PNG => 'image/png',
            self::JPEG => 'image/jpeg',
            self::WEBP => 'image/webp',
        };
    }

    public function write(GdImage $image, ?string $path = null): void
    {
        match ($this) {
            self::PNG => imagepng($image, $path),
            self::JPEG => imagejpeg($image, $path),
            self::WEBP => imagewebp($image, $path),
        };
    }
}
