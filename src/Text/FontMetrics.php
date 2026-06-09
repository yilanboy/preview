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
    ) {}

    /**
     * Total line height (ascent + descent).
     */
    public function height(): int
    {
        return $this->ascent + $this->descent;
    }
}
