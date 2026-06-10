<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Canvas\Background;

use GdImage;
use Yilanboy\Preview\Canvas\Enums\GradientDirection;
use Yilanboy\Preview\ColorConverter;
use Yilanboy\Preview\Exceptions\InvalidInput;

final readonly class Gradient implements Background
{
    public function __construct(
        public string $from,
        public string $to,
        public GradientDirection $direction = GradientDirection::Vertical,
    ) {
        if (! ColorConverter::isValidColor($from)) {
            throw new InvalidInput("Invalid gradient color: {$from}");
        }

        if (! ColorConverter::isValidColor($to)) {
            throw new InvalidInput("Invalid gradient color: {$to}");
        }
    }

    public function draw(GdImage $image, int $width, int $height): void
    {
        $from = ColorConverter::hexToRgb(ColorConverter::toHex($this->from));
        $to = ColorConverter::hexToRgb(ColorConverter::toHex($this->to));

        match ($this->direction) {
            GradientDirection::Vertical => $this->fillVertical($image, $width,
                $height, $from, $to),
            GradientDirection::Horizontal => $this->fillHorizontal($image,
                $width, $height, $from, $to),
            GradientDirection::Diagonal => $this->fillDiagonal($image, $width,
                $height, $from, $to),
        };
    }

    /**
     * @param  array{0: int<0, 255>, 1: int<0, 255>, 2: int<0, 255>}  $from
     * @param  array{0: int<0, 255>, 1: int<0, 255>, 2: int<0, 255>}  $to
     */
    private function fillVertical(
        GdImage $image,
        int $width,
        int $height,
        array $from,
        array $to
    ): void {
        $denominator = max($height - 1, 1);

        for ($y = 0; $y < $height; $y++) {
            $color = $this->interpolate($from, $to, $y / $denominator);
            imageline(image: $image, x1: 0, y1: $y, x2: $width - 1, y2: $y,
                color: $color);
        }
    }

    /**
     * @param  array{0: int<0, 255>, 1: int<0, 255>, 2: int<0, 255>}  $from
     * @param  array{0: int<0, 255>, 1: int<0, 255>, 2: int<0, 255>}  $to
     */
    private function fillHorizontal(
        GdImage $image,
        int $width,
        int $height,
        array $from,
        array $to
    ): void {
        $denominator = max($width - 1, 1);

        for ($x = 0; $x < $width; $x++) {
            $color = $this->interpolate($from, $to, $x / $denominator);
            imageline(image: $image, x1: $x, y1: 0, x2: $x, y2: $height - 1,
                color: $color);
        }
    }

    /**
     * @param  array{0: int<0, 255>, 1: int<0, 255>, 2: int<0, 255>}  $from
     * @param  array{0: int<0, 255>, 1: int<0, 255>, 2: int<0, 255>}  $to
     */
    private function fillDiagonal(
        GdImage $image,
        int $width,
        int $height,
        array $from,
        array $to
    ): void {
        $denominator = max($width + $height - 2, 1);

        for ($k = 0; $k <= $width + $height - 2; $k++) {
            $color = $this->interpolate($from, $to, $k / $denominator);

            $x1 = min($k, $width - 1);
            $y1 = $k - $x1;
            $x2 = max(0, $k - ($height - 1));
            $y2 = $k - $x2;

            imageline(image: $image, x1: $x1, y1: $y1, x2: $x2, y2: $y2,
                color: $color);
        }
    }

    /**
     * @param  array{0: int<0, 255>, 1: int<0, 255>, 2: int<0, 255>}  $from
     * @param  array{0: int<0, 255>, 1: int<0, 255>, 2: int<0, 255>}  $to
     */
    private function interpolate(array $from, array $to, float $t): int
    {
        $r = (int) round($from[0] + ($to[0] - $from[0]) * $t);
        $g = (int) round($from[1] + ($to[1] - $from[1]) * $t);
        $b = (int) round($from[2] + ($to[2] - $from[2]) * $t);

        return ($r << 16) | ($g << 8) | $b;
    }
}
