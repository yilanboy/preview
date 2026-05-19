<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Image\Background;

use GdImage;
use RuntimeException;
use Yilanboy\Preview\Color\Converter;

final readonly class Solid implements Background
{
    public function __construct(public string $color) {}

    public function apply(GdImage $image, int $width, int $height, Converter $converter): void
    {
        $rgb = $converter->hexToRgb($converter->toHex($this->color));
        $allocated = imagecolorallocate($image, ...$rgb);

        if ($allocated === false) {
            throw new RuntimeException('Failed to allocate background color');
        }

        imagefill($image, 0, 0, $allocated);
    }
}
