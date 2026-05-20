<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Canvas\Enums;

enum Size
{
    case OpenGraph;
    case Square;

    public function width(): int
    {
        return match ($this) {
            self::OpenGraph => 1200,
            self::Square => 1080,
        };
    }

    public function height(): int
    {
        return match ($this) {
            self::OpenGraph => 630,
            self::Square => 1080,
        };
    }
}
