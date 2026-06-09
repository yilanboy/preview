<?php

declare(strict_types=1);

namespace Yilanboy\Preview;

use InvalidArgumentException;

final class ColorConverter
{
    /**
     * Supported color names mapped to their hex codes.
     *
     * @var array<string, string>
     */
    private const array NAMES = [
        'red' => '#ff0000',
        'green' => '#00ff00',
        'blue' => '#0000ff',
        'yellow' => '#ffff00',
        'orange' => '#ffa500',
        'white' => '#ffffff',
        'black' => '#000000',
    ];

    /**
     * Check the string is a color hex format
     */
    public static function isValidHex(string $hex): bool
    {
        return preg_match('/^#[a-f0-9]{6}$/i', $hex) === 1;
    }

    /**
     * Check the string is a supported color: either a known color name
     * (e.g. white, black) or a valid hex code.
     */
    public static function isValidColor(string $color): bool
    {
        return self::isValidHex($color) || isset(self::NAMES[strtolower($color)]);
    }

    /**
     * Convert color hex code to RGB
     *
     * @return array{0: int<0, 255>, 1: int<0, 255>, 2: int<0, 255>}
     */
    public static function hexToRgb(string $hex): array
    {
        if (! self::isValidHex($hex)) {
            throw new InvalidArgumentException('Invalid hex color');
        }

        /** @var array{0: int<0, 255>, 1: int<0, 255>, 2: int<0, 255>} $rgb */
        $rgb = sscanf($hex, '#%02x%02x%02x');

        return $rgb;
    }

    /**
     * Convert color name to hex code
     */
    public static function nameToHex(string $word): string
    {
        return self::NAMES[strtolower($word)]
            ?? throw new InvalidArgumentException('Invalid color name');
    }

    /**
     * Normalize a hex code or color name to a hex code.
     */
    public static function toHex(string $color): string
    {
        return self::isValidHex($color) ? $color : self::nameToHex($color);
    }
}
