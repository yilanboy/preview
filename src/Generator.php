<?php

declare(strict_types=1);

namespace Yilanboy\Preview;

use GdImage;
use InvalidArgumentException;
use RuntimeException;
use Yilanboy\Preview\Canvas\Background\Background;
use Yilanboy\Preview\Canvas\Background\Solid;
use Yilanboy\Preview\Text\Enums\Alignment;
use Yilanboy\Preview\Text\Enums\Position;
use Yilanboy\Preview\Text\TextBlock;
use Yilanboy\Preview\Text\Writer;

final class Generator
{
    private const float MARGIN_RATIO = 0.05;

    private int $width = 1200;

    private int $height = 600;

    private Background $background;

    private ?TextBlock $title = null;

    private ?TextBlock $description = null;

    public function __construct(
        private readonly ColorConverter $converter = new ColorConverter(),
        private readonly Writer $writer = new Writer(),
    ) {
        $this->background = new Solid('#f9fafb');
    }

    public function size(int $width, int $height): self
    {
        if ($width <= 0 || $height <= 0) {
            throw new InvalidArgumentException('Width and height must be positive');
        }

        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    public function background(Background $background): self
    {
        $this->background = $background;

        return $this;
    }

    public function backgroundColor(string $color): self
    {
        return $this->background(new Solid($color));
    }

    public function title(TextBlock $block): self
    {
        $this->title = $block;

        return $this;
    }

    public function description(TextBlock $block): self
    {
        $this->description = $block;

        return $this;
    }

    public function output(): void
    {
        $image = $this->render();

        header('Content-Type: image/png');
        imagepng($image);
    }

    public function save(string $path): void
    {
        $image = $this->render();

        imagepng($image, $path);
    }

    private function render(): GdImage
    {
        if ($this->width < 1 || $this->height < 1) {
            throw new RuntimeException('Width and height must be at least 1');
        }

        $image = imagecreatetruecolor($this->width, $this->height);

        if ($image === false) {
            throw new RuntimeException('Failed to create image canvas');
        }

        $this->background->draw($image, $this->width, $this->height, $this->converter);

        if ($this->title !== null) {
            $this->drawTextBlock($image, $this->title, $this->title->position ?? Position::Top);
        }

        if ($this->description !== null) {
            $this->drawTextBlock($image, $this->description, $this->description->position ?? Position::Center);
        }

        return $image;
    }

    private function drawTextBlock(GdImage $image, TextBlock $block, Position $position): void
    {
        $fontPath = $block->font->path();
        $fontSize = $block->fontSize->value;
        $maxWidth = intval($this->width - $this->width * self::MARGIN_RATIO * 2);

        $wrappedText = $this->writer->wrapTextImage(
            text: $block->text,
            fontSize: $fontSize,
            fontPath: $fontPath,
            maxWidth: $maxWidth,
        );

        $bbox = imagettfbbox($fontSize, 0, $fontPath, $wrappedText);

        if ($bbox === false) {
            throw new RuntimeException('Failed to calculate text bounding box');
        }

        $textHeight = $bbox[1] - $bbox[5];
        $textWidth = $bbox[2] - $bbox[0];

        imagettftext(
            image: $image,
            size: $fontSize,
            angle: 0,
            x: $this->resolveX($block->alignment, $textWidth),
            y: $this->resolveY($position, $textHeight, $bbox),
            color: $this->allocateColor($image, $this->converter->toHex($block->color)),
            font_filename: $fontPath,
            text: $wrappedText,
        );
    }

    private function resolveX(Alignment $alignment, int $textWidth): int
    {
        $margin = intval($this->width * self::MARGIN_RATIO);

        return match ($alignment) {
            Alignment::Left => $margin,
            Alignment::Center => intval(($this->width - $textWidth) / 2),
            Alignment::Right => $this->width - $textWidth - $margin,
        };
    }

    /**
     * @param  array<int, int>  $bbox
     */
    private function resolveY(Position $position, int $textHeight, array $bbox): int
    {
        return match ($position) {
            Position::Top => intval($this->height / 3 - $textHeight / 2),
            Position::Center => intval(($this->height - $textHeight) / 2 - $bbox[5]),
            Position::Bottom => intval(2 * $this->height / 3 - ($bbox[1] + $bbox[5]) / 2),
        };
    }

    private function allocateColor(GdImage $image, string $hex): int
    {
        $color = imagecolorallocate($image, ...$this->converter->hexToRgb($hex));

        if ($color === false) {
            throw new RuntimeException('Failed to allocate color');
        }

        return $color;
    }
}
