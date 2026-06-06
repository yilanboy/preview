<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Text;

/**
 * A single line of text resolved to its final canvas position, ready to stamp.
 * The x/y are the coordinates GD's imagettftext() expects: x is the left edge
 * after alignment, y is the glyph baseline.
 */
final readonly class PlacedLine
{
    public function __construct(
        public int $x,
        public int $y,
        public string $text,
        public int $fontSize,
        public string $fontPath,
        public string $color,
    ) {}
}
