<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Canvas\Enums;

enum Size
{
    case OpenGraph;
    case Square;
    case Landscape;
    case Portrait;
    case YouTube;

    /** @return positive-int */
    public function width(): int
    {
        return match ($this) {
            self::OpenGraph => 1200,
            self::Square, self::Portrait => 1080,
            self::Landscape => 1920,
            self::YouTube => 1280,
        };
    }

    /** @return positive-int */
    public function height(): int
    {
        return match ($this) {
            self::OpenGraph => 630,
            self::Square, self::Landscape => 1080,
            self::Portrait => 1920,
            self::YouTube => 720,
        };
    }
}
