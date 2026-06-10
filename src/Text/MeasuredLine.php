<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Text;

/**
 * A wrapped line of text together with its pixel width, measured once during
 * wrapping so placement never has to re-measure the same string.
 */
final readonly class MeasuredLine
{
    public function __construct(
        public string $text,
        public int $width,
    ) {}
}
