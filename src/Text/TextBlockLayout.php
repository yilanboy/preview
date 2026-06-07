<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Text;

use Yilanboy\Preview\Text\Enums\Alignment;
use Yilanboy\Preview\Text\Enums\Position;

/**
 * A measured TextBlock: wrapped lines plus the vertical metrics needed to place
 * and render it on the canvas.
 */
final readonly class TextBlockLayout
{
    /**
     * @param  array<int, string>  $lines
     */
    public function __construct(
        public string $fontPath,
        public int $fontSize,
        public array $lines,
        public int $lineAdvance,
        public int $ascent,
        public int $height,
        public Position $position,
        public string $color,
        public Alignment $alignment,
    ) {}
}
