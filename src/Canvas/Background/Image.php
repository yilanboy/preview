<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Canvas\Background;

use GdImage;
use InvalidArgumentException;
use RuntimeException;
use Yilanboy\Preview\Canvas\Enums\ImageFit;
use Yilanboy\Preview\Contracts\Background;

final readonly class Image implements Background
{
    public function __construct(
        public string $path,
        public ImageFit $fit = ImageFit::Cover,
        public float $opacity = 1.0,
        public string $tint = '#000000',
    ) {
        if ($opacity < 0.0 || $opacity > 1.0) {
            throw new InvalidArgumentException('Opacity must be between 0.0 and 1.0');
        }

        if (! file_exists($path)) {
            throw new InvalidArgumentException("Background image not found: {$path}");
        }
    }

    public function draw(GdImage $image, int $width, int $height): void
    {
        // When opacity < 1, the tint shows through the partial transparency.
        if ($this->opacity < 1.0) {
            new Solid($this->tint)->draw($image, $width, $height);
        }

        $contents = file_get_contents($this->path);

        if ($contents === false) {
            throw new RuntimeException("Failed to read background image: {$this->path}");
        }

        $src = imagecreatefromstring($contents);

        if ($src === false) {
            throw new RuntimeException("Failed to decode background image: {$this->path}");
        }

        $srcWidth = imagesx($src);
        $srcHeight = imagesy($src);
        $pct = (int) round($this->opacity * 100);

        match ($this->fit) {
            ImageFit::Stretch => $this->stretch($image, $src, $width, $height, $srcWidth, $srcHeight, $pct),
            ImageFit::Cover => $this->cover($image, $src, $width, $height, $srcWidth, $srcHeight, $pct),
            ImageFit::Contain => $this->contain($image, $src, $width, $height, $srcWidth, $srcHeight, $pct),
            ImageFit::Tile => $this->tile($image, $src, $width, $height, $srcWidth, $srcHeight, $pct),
        };
    }

    private function stretch(
        GdImage $dst,
        GdImage $src,
        int $dstWidth,
        int $dstHeight,
        int $srcWidth,
        int $srcHeight,
        int $pct
    ): void {
        $this->blendResized($dst, $src, 0, 0, $dstWidth, $dstHeight, $srcWidth, $srcHeight, $pct);
    }

    private function cover(
        GdImage $dst,
        GdImage $src,
        int $dstWidth,
        int $dstHeight,
        int $srcWidth,
        int $srcHeight,
        int $pct
    ): void {
        $scale = max($dstWidth / $srcWidth, $dstHeight / $srcHeight);
        $newWidth = (int) round($srcWidth * $scale);
        $newHeight = (int) round($srcHeight * $scale);
        $offsetX = (int) round(($dstWidth - $newWidth) / 2);
        $offsetY = (int) round(($dstHeight - $newHeight) / 2);

        $this->blendResized($dst, $src, $offsetX, $offsetY, $newWidth, $newHeight, $srcWidth, $srcHeight, $pct);
    }

    private function contain(
        GdImage $dst,
        GdImage $src,
        int $dstWidth,
        int $dstHeight,
        int $srcWidth,
        int $srcHeight,
        int $pct
    ): void {
        $scale = min($dstWidth / $srcWidth, $dstHeight / $srcHeight);
        $newWidth = (int) round($srcWidth * $scale);
        $newHeight = (int) round($srcHeight * $scale);
        $offsetX = (int) round(($dstWidth - $newWidth) / 2);
        $offsetY = (int) round(($dstHeight - $newHeight) / 2);

        $this->blendResized($dst, $src, $offsetX, $offsetY, $newWidth, $newHeight, $srcWidth, $srcHeight, $pct);
    }

    private function tile(
        GdImage $dst,
        GdImage $src,
        int $dstWidth,
        int $dstHeight,
        int $srcWidth,
        int $srcHeight,
        int $pct
    ): void {
        for ($y = 0; $y < $dstHeight; $y += $srcHeight) {
            for ($x = 0; $x < $dstWidth; $x += $srcWidth) {
                if ($pct === 100) {
                    imagecopy($dst, $src, $x, $y, 0, 0, $srcWidth, $srcHeight);

                    continue;
                }

                imagecopymerge($dst, $src, $x, $y, 0, 0, $srcWidth, $srcHeight, $pct);
            }
        }
    }

    private function blendResized(
        GdImage $dst,
        GdImage $src,
        int $dstX,
        int $dstY,
        int $newWidth,
        int $newHeight,
        int $srcWidth,
        int $srcHeight,
        int $pct
    ): void {
        if ($pct === 100) {
            imagecopyresampled($dst, $src, $dstX, $dstY, 0, 0, $newWidth, $newHeight, $srcWidth, $srcHeight);

            return;
        }

        if ($newWidth < 1 || $newHeight < 1) {
            throw new RuntimeException('Resized image dimensions must be at least 1');
        }

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        if ($resized === false) {
            throw new RuntimeException('Failed to allocate intermediate image buffer');
        }

        imagecopyresampled($resized, $src, 0, 0, 0, 0, $newWidth, $newHeight, $srcWidth, $srcHeight);
        imagecopymerge($dst, $resized, $dstX, $dstY, 0, 0, $newWidth, $newHeight, $pct);
    }
}
