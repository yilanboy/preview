<?php

declare(strict_types=1);

namespace Yilanboy\Preview;

use GdImage;
use RuntimeException;
use Yilanboy\Preview\Canvas\Background\Background;
use Yilanboy\Preview\Canvas\Background\Solid;
use Yilanboy\Preview\Canvas\Enums\Margin;
use Yilanboy\Preview\Canvas\Enums\Size;
use Yilanboy\Preview\Text\BlockLayout;
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

        // Blocks sharing a position are stacked (title above description) and
        // anchored as a single group, so they never overlap.
        $groups = [];
        foreach (array_filter([$this->title, $this->description]) as $block) {
            $groups[$block->position->name][] = $this->layoutBlock($block);
        }

        foreach ($groups as $group) {
            $this->drawGroup($image, $group);
        }

        return $image;
    }

    /**
     * Measure a block: wrap its text and compute the metrics needed to place
     * and render it.
     */
    private function layoutBlock(TextBlock $block): BlockLayout
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

        // baseline-to-baseline distance (CSS-style line height)
        $lineAdvance = (int) round($fontSize * $block->lineHeight->multiplier());
        // uniform vertical metrics so every line shares the same height
        $metrics = $this->writer->lineMetrics($fontSize, $fontPath);
        $height = $metrics['height'] + $lineAdvance * (count($lines) - 1);

        return new BlockLayout(
            fontPath: $fontPath,
            fontSize: $fontSize,
            lines: $lines,
            lineAdvance: $lineAdvance,
            ascent: $metrics['ascent'],
            height: $height,
            position: $block->position,
            color: $block->color,
            alignment: $block->alignment,
        );
    }

    /**
     * Anchor a group of stacked blocks at their shared position and draw them
     * top to bottom, separated by the gap below each block.
     *
     * @param  array<int, BlockLayout>  $group
     */
    private function drawGroup(GdImage $image, array $group): void
    {
        $lastIndex = count($group) - 1;

        $groupHeight = 0;
        foreach ($group as $i => $item) {
            $groupHeight += $item->height;
            if ($i < $lastIndex) {
                $groupHeight += $this->gapAfter($item->fontSize);
            }
        }

        $cursor = match ($group[0]->position) {
            Position::Top => $this->margin->value,
            Position::Center => intval(($this->height - $groupHeight) / 2),
            Position::Bottom => $this->height - $this->margin->value - $groupHeight,
        };

        foreach ($group as $i => $item) {
            $this->drawBlock($image, $item, $cursor);
            $cursor += $item->height;
            if ($i < $lastIndex) {
                $cursor += $this->gapAfter($item->fontSize);
            }
        }
    }

    /**
     * Vertical spacing placed below a block when another block is stacked
     * beneath it. Tied to the block's font size — not its line-height, which
     * governs spacing *within* a block rather than *between* blocks.
     */
    private function gapAfter(int $fontSize): int
    {
        return intval($fontSize * 0.6);
    }

    /**
     * Render a single block's lines starting at the given top edge.
     */
    private function drawBlock(GdImage $image, BlockLayout $item, int $top): void
    {
        $color = $this->allocateColor($image, $this->converter->toHex($item->color));

        foreach ($item->lines as $i => $line) {
            $lineWidth = $this->writer->calculateTextImageWidth($line, $item->fontSize, $item->fontPath);

            imagettftext(
                image: $image,
                size: $item->fontSize,
                angle: 0,
                x: $this->resolveX($item->alignment, $lineWidth),
                y: $top + $item->ascent + $i * $item->lineAdvance,
                color: $color,
                font_filename: $item->fontPath,
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

    private function allocateColor(GdImage $image, string $hex): int
    {
        $color = imagecolorallocate($image, ...$this->converter->hexToRgb($hex));

        if ($color === false) {
            throw new RuntimeException('Failed to allocate color');
        }

        return $color;
    }
}
