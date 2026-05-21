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

    public function multiplier(): float
    {
        return match ($this) {
            self::Tight => 1.0,
            self::Snug => 1.15,
            self::Normal => 1.3,
            self::Relaxed => 1.5,
            self::Loose => 1.75,
        };
    }
}
