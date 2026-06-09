<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Text;

use GdImage;
use RuntimeException;
use Yilanboy\Preview\ColorConverter;

final readonly class Writer
{
    /**
     * Draw a single placed line onto the canvas at its resolved baseline.
     */
    public function stamp(GdImage $image, LinePosition $line): void
    {
        $result = imagettftext(
            image: $image,
            size: $line->fontSize,
            angle: 0,
            x: $line->x,
            y: $line->y,
            color: $this->allocateColor($image, ColorConverter::toHex($line->color)),
            font_filename: $line->fontPath,
            text: $line->text,
        );

        if ($result === false) {
            throw new RuntimeException('Failed to render text onto the image');
        }
    }

    public function allocateColor(GdImage $image, string $hex): int
    {
        $color = imagecolorallocate($image, ...ColorConverter::hexToRgb($hex));

        if ($color === false) {
            throw new RuntimeException('Failed to allocate color');
        }

        return $color;
    }
}
