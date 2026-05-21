<?php

declare(strict_types=1);

namespace Yilanboy\Preview;

use GdImage;
use RuntimeException;
use Yilanboy\Preview\Canvas\Background\Background;
use Yilanboy\Preview\Canvas\Background\Solid;
use Yilanboy\Preview\Canvas\Enums\Margin;
use Yilanboy\Preview\Canvas\Enums\Size;
use Yilanboy\Preview\Text\Enums\Alignment;
use Yilanboy\Preview\Text\Enums\Position;
use Yilanboy\Preview\Text\TextBlock;
use Yilanboy\Preview\Text\Writer;

final class Generator
{
    private int $width;

    private int $height;

    private Margin $margin = Margin::Medium;

    private ?TextBlock $title = null;

    private ?TextBlock $description = null;

    public function __construct(
        private Background $background = new Solid(color: '#f9fafb'),
        private readonly ColorConverter $converter = new ColorConverter,
        private readonly Writer $writer = new Writer,
    ) {
        $this->size(Size::OpenGraph);
    }

    public function size(Size $size): self
    {
        $this->width = $size->width();
        $this->height = $size->height();

        return $this;
    }

    public function background(Background $background): self
    {
        $this->background = $background;

        return $this;
    }

    public function margin(Margin $margin): self
    {
        $this->margin = $margin;

        return $this;
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
        $maxWidth = $this->width - $this->margin->value * 2;

        $lines = $this->writer->wrapText(
            text: $block->text,
            fontSize: $fontSize,
            fontPath: $fontPath,
            maxWidth: $maxWidth,
        );

        $metrics = imagettfbbox($fontSize, 0, $fontPath, $lines[0]);

        if ($metrics === false) {
            throw new RuntimeException('Failed to calculate text bounding box');
        }

        $lineGlyphHeight = $metrics[1] - $metrics[5];
        $lineSpacing = $fontSize * $block->lineHeight->multiplier();
        $blockHeight = intval($lineGlyphHeight + (count($lines) - 1) * $lineSpacing);
        $baselineY = $this->resolveY($position, $blockHeight, $metrics);
        $color = $this->allocateColor($image, $this->converter->toHex($block->color));

        foreach ($lines as $i => $line) {
            $lineWidth = $this->writer->calculateTextImageWidth($line, $fontSize, $fontPath);

            imagettftext(
                image: $image,
                size: $fontSize,
                angle: 0,
                x: $this->resolveX($block->alignment, $lineWidth),
                y: intval($baselineY + $i * $lineSpacing),
                color: $color,
                font_filename: $fontPath,
                text: $line,
            );
        }
    }

    private function resolveX(Alignment $alignment, int $textWidth): int
    {
        return match ($alignment) {
            Alignment::Left => $this->margin->value,
            Alignment::Center => intval(($this->width - $textWidth) / 2),
            Alignment::Right => $this->width - $textWidth - $this->margin->value,
        };
    }

    /**
     * @param  array<int, int>  $metrics
     */
    private function resolveY(Position $position, int $blockHeight, array $metrics): int
    {
        return match ($position) {
            Position::Top => intval($this->height / 3 - $blockHeight / 2),
            Position::Center => intval(($this->height - $blockHeight) / 2 - $metrics[5]),
            Position::Bottom => intval(2 * $this->height / 3 - $blockHeight / 2 - $metrics[5]),
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
