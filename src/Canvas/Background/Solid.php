<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Canvas\Background;

use GdImage;
use InvalidArgumentException;
use RuntimeException;
use Yilanboy\Preview\ColorConverter;
use Yilanboy\Preview\Contracts\Background;

final readonly class Solid implements Background
{
    public function __construct(public string $color)
    {
        if (! ColorConverter::isValidColor($color)) {
            throw new InvalidArgumentException("Invalid color: {$color}");
        }
    }

    public function draw(GdImage $image, int $width, int $height): void
    {
        $rgb = ColorConverter::hexToRgb(ColorConverter::toHex($this->color));
        $allocated = imagecolorallocate($image, ...$rgb);

        if ($allocated === false) {
            throw new RuntimeException('Failed to allocate background color');
        }

        imagefill($image, 0, 0, $allocated);
    }
}
