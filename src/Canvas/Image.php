<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Canvas;

use GdImage;
use Yilanboy\Preview\Canvas\Enums\ImageFit;
use Yilanboy\Preview\ColorConverter;
use Yilanboy\Preview\Exceptions\InvalidInput;
use Yilanboy\Preview\Exceptions\RenderFailure;

final class Image implements Background
{
    private ?GdImage $decoded = null;

    public function __construct(
        public readonly string $path,
        public readonly ImageFit $fit = ImageFit::Cover,
        public readonly float $opacity = 1.0,
        public readonly string $tint = '#000000',
    ) {
        if ($opacity < 0.0 || $opacity > 1.0) {
            throw new InvalidInput('Opacity must be between 0.0 and 1.0');
        }

        if (! is_file($path) || ! is_readable($path)) {
            throw new InvalidInput("Background image not found: {$path}");
        }

        if (! ImageValidator::isValidImage($path)) {
            throw new InvalidInput("Invalid background image: {$path}");
        }

        if (! ColorConverter::isValidColor($tint)) {
            throw new InvalidInput("Invalid color: {$tint}");
        }
    }

    public function draw(GdImage $image, int $width, int $height): void
    {
        // When opacity < 1, the tint shows through the partial transparency.
        if ($this->opacity < 1.0) {
            new Solid($this->tint)->draw($image, $width, $height);
        }

        $src = $this->decoded();

        $srcWidth = imagesx($src);
        $srcHeight = imagesy($src);
        $pct = (int) round($this->opacity * 100);

        match ($this->fit) {
            ImageFit::Stretch => $this->stretch($image, $src, $width, $height,
                $srcWidth, $srcHeight, $pct),
            ImageFit::Cover => $this->cover($image, $src, $width, $height,
                $srcWidth, $srcHeight, $pct),
            ImageFit::Contain => $this->contain($image, $src, $width, $height,
                $srcWidth, $srcHeight, $pct),
            ImageFit::Tile => $this->tile($image, $src, $width, $height,
                $srcWidth, $srcHeight, $pct),
        };
    }

    /**
     * Decode the source image once and cache it, so rendering many canvases
     * from the same background doesn't re-read and re-decode the file.
     */
    private function decoded(): GdImage
    {
        if ($this->decoded !== null) {
            return $this->decoded;
        }

        $contents = file_get_contents($this->path);

        if ($contents === false) {
            throw new RenderFailure("Failed to read background image: {$this->path}");
        }

        $src = imagecreatefromstring($contents);

        if ($src === false) {
            throw new RenderFailure("Failed to decode background image: {$this->path}");
        }

        return $this->decoded = $src;
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
        $this->blendResized($dst, $src, 0, 0, $dstWidth, $dstHeight, $srcWidth,
            $srcHeight, $pct);
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

        $this->blendResized($dst, $src, $offsetX, $offsetY, $newWidth,
            $newHeight, $srcWidth, $srcHeight, $pct);
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

        $this->blendResized($dst, $src, $offsetX, $offsetY, $newWidth,
            $newHeight, $srcWidth, $srcHeight, $pct);
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

                imagecopymerge($dst, $src, $x, $y, 0, 0, $srcWidth, $srcHeight,
                    $pct);
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
            imagecopyresampled($dst, $src, $dstX, $dstY, 0, 0, $newWidth,
                $newHeight, $srcWidth, $srcHeight);

            return;
        }

        if ($newWidth < 1 || $newHeight < 1) {
            throw new RenderFailure('Resized image dimensions must be at least 1');
        }

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        if ($resized === false) {
            throw new RenderFailure('Failed to allocate intermediate image buffer');
        }

        imagecopyresampled($resized, $src, 0, 0, 0, 0, $newWidth, $newHeight,
            $srcWidth, $srcHeight);
        imagecopymerge($dst, $resized, $dstX, $dstY, 0, 0, $newWidth,
            $newHeight, $pct);
    }
}
