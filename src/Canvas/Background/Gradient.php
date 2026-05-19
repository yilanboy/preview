<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Canvas\Background;

use GdImage;
use Yilanboy\Preview\Canvas\Enums\GradientDirection;
use Yilanboy\Preview\ColorConverter;

final readonly class Gradient implements Background
{
    public function __construct(
        public string $from,
        public string $to,
        public GradientDirection $direction = GradientDirection::Vertical,
    ) {}

    public function draw(GdImage $image, int $width, int $height, ColorConverter $converter): void
    {
        $from = $converter->hexToRgb($converter->toHex($this->from));
        $to = $converter->hexToRgb($converter->toHex($this->to));

        match ($this->direction) {
            GradientDirection::Vertical => $this->fillVertical($image, $width, $height, $from, $to),
            GradientDirection::Horizontal => $this->fillHorizontal($image, $width, $height, $from, $to),
            GradientDirection::Diagonal => $this->fillDiagonal($image, $width, $height, $from, $to),
        };
    }

    /**
     * @param  array{0: int<0, 255>, 1: int<0, 255>, 2: int<0, 255>}  $from
     * @param  array{0: int<0, 255>, 1: int<0, 255>, 2: int<0, 255>}  $to
     */
    private function fillVertical(GdImage $image, int $width, int $height, array $from, array $to): void
    {
        $denominator = max($height - 1, 1);

        for ($y = 0; $y < $height; $y++) {
            $color = $this->interpolate($from, $to, $y / $denominator);
            imageline($image, 0, $y, $width - 1, $y, $color);
        }
    }

    /**
     * @param  array{0: int<0, 255>, 1: int<0, 255>, 2: int<0, 255>}  $from
     * @param  array{0: int<0, 255>, 1: int<0, 255>, 2: int<0, 255>}  $to
     */
    private function fillHorizontal(GdImage $image, int $width, int $height, array $from, array $to): void
    {
        $denominator = max($width - 1, 1);

        for ($x = 0; $x < $width; $x++) {
            $color = $this->interpolate($from, $to, $x / $denominator);
            imageline($image, $x, 0, $x, $height - 1, $color);
        }
    }

    /**
     * @param  array{0: int<0, 255>, 1: int<0, 255>, 2: int<0, 255>}  $from
     * @param  array{0: int<0, 255>, 1: int<0, 255>, 2: int<0, 255>}  $to
     */
    private function fillDiagonal(GdImage $image, int $width, int $height, array $from, array $to): void
    {
        $denominator = max($width + $height - 2, 1);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $color = $this->interpolate($from, $to, ($x + $y) / $denominator);
                imagesetpixel($image, $x, $y, $color);
            }
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
