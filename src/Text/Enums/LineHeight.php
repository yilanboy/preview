<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Text\Enums;

enum LineHeight
{
    case Tight;
    case Snug;
    case Normal;
    case Relaxed;
    case Loose;

    /**
     * Factor applied to the font's natural single-line box. 1.0 is single
     * spacing (lines exactly clear, no overlap); larger values add space.
     */
    public function multiplier(): float
    {
        return match ($this) {
            self::Tight => 1.0,
            self::Snug => 1.1,
            self::Normal => 1.25,
            self::Relaxed => 1.45,
            self::Loose => 1.7,
        };
    }
}
