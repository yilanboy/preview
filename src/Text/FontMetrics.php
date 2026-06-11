<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Text;

/**
 * Encapsulates the resolved vertical metrics of a font at a specific size.
 */
final readonly class FontMetrics
{
    public function __construct(
        public int $ascent,
        public int $descent,
        public int $lineGap = 0,
    ) {}

    /**
     * Glyph extent (ascent + descent) — a single line's actual vertical pixel
     * bounds.
     */
    public function height(): int
    {
        return $this->ascent + $this->descent;
    }

    /**
     * The font's natural single-line box (glyph extent plus the designer's
     * line gap) — the base that a LineHeight multiplier scales.
     */
    public function lineHeight(): int
    {
        return $this->ascent + $this->descent + $this->lineGap;
    }
}
