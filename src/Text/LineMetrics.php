<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Text;

/**
 * A font's declared vertical metrics in font design units. The descender is
 * negative (it sits below the baseline).
 */
final readonly class LineMetrics
{
    public function __construct(
        public int $unitsPerEm,
        public int $ascender,
        public int $descender,
        public int $lineGap
    ) {}
}
