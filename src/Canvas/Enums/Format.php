<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Canvas\Enums;

use GdImage;
use Yilanboy\Preview\Exceptions\RenderFailure;

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

    /** @param  int<0, 100>|null  $quality */
    public function write(GdImage $image, ?string $path = null, ?int $quality = null): void
    {
        $success = match ($this) {
            self::PNG => imagepng($image, $path),
            self::JPEG => imagejpeg($image, $path, $quality ?? -1),
            self::WEBP => imagewebp($image, $path, $quality ?? -1),
        };

        if (! $success) {
            throw new RenderFailure("Failed to write image in {$this->name} format");
        }
    }
}
