<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Text;

use Yilanboy\Preview\Text\Enums\Alignment;
use Yilanboy\Preview\Text\Enums\Font;
use Yilanboy\Preview\Text\Enums\Position;

/**
 * Places text blocks onto a canvas plane: wraps and measures each block, then
 * resolves every line to its final baseline coordinate. Pure geometry — it
 * never touches a GdImage, so its output can be asserted directly.
 */
final readonly class TextPlacer
{
    public function __construct(private Writer $writer = new Writer) {}

    /**
     * Resolve blocks to their placed lines on a canvas of the given size.
     *
     * Blocks sharing a Position are stacked in input order (e.g. title above
     * description) and anchored as a single group, so they never overlap.
     *
     * @param  array<int, TextBlock>  $blocks
     * @return array<int, LinePosition>
     */
    public function place(int $width, int $height, int $margin, array $blocks): array
    {
        /* @var array<string<'TOP', 'CENTER', 'BOTTOM'>, array<TextBlockLayout>> $positionGroups */
        $positionGroups = [];
        foreach ($blocks as $block) {
            $positionGroups[$block->position->name][] = $this->measure($block, $width, $margin);
        }

        $placed = [];
        // A single position holds a stack of one or more block layouts.
        foreach ($positionGroups as $positionGroup) {
            foreach ($this->placeStack($positionGroup, $width, $height, $margin) as $line) {
                $placed[] = $line;
            }
        }

        return $placed;
    }

    /**
     * Measure a block: wrap its text and compute the metrics needed to place
     * and render it.
     */
    private function measure(TextBlock $block, int $width, int $margin): TextBlockLayout
    {
        $fontPath = $block->font instanceof Font ? $block->font->path() : $block->font;
        $fontSize = $block->fontSize->value;
        $maxWidth = $width - $margin * 2;

        $lines = $this->writer->wrapText(
            text: $block->text,
            fontSize: $fontSize,
            fontPath: $fontPath,
            maxWidth: $maxWidth,
        );

        // baseline-to-baseline distance (CSS-style line height)
        $lineAdvance = (int) round($fontSize * $block->lineHeight->multiplier());
        // uniform vertical metrics so every line shares the same height
        $boundingBox = $this->writer->lineBoundingBox($fontSize, $fontPath);
        $ascent = -$boundingBox[7];  // top of glyph above baseline (bbox[7] is negative)
        $descent = $boundingBox[1];  // below baseline
        $height = $ascent + $descent;
        $blockHeight = $height + $lineAdvance * (count($lines) - 1);

        return new TextBlockLayout(
            fontPath: $fontPath,
            fontSize: $fontSize,
            lines: $lines,
            lineAdvance: $lineAdvance,
            ascent: $ascent,
            height: $blockHeight,
            position: $block->position,
            color: $block->color,
            alignment: $block->alignment,
        );
    }

    /**
     * Anchor a group of stacked blocks at their shared position and place them
     * top to bottom, separated by the gap below each block.
     *
     * @param  array<int, TextBlockLayout>  $positionGroup
     * @return array<int, LinePosition>
     */
    private function placeStack(array $positionGroup, int $width, int $height, int $margin): array
    {
        $lastIndex = count($positionGroup) - 1;

        // Measure the whole stack's height (each block's height plus the gaps
        // between them) so resolveTop can anchor the stack as a single unit when
        // centering or bottom-aligning. Typically a title stacked over a description.
        $groupHeight = 0;
        foreach ($positionGroup as $i => $textBlockLayout) {
            $groupHeight += $textBlockLayout->height;
            if ($i < $lastIndex) {
                $groupHeight += $this->gapAfter($textBlockLayout->fontSize);
            }
        }

        $cursorY = $this->resolveTop($positionGroup[0]->position, $margin, $height, $groupHeight);

        $placed = [];
        foreach ($positionGroup as $i => $textBlockLayout) {
            foreach ($this->placeBlock($textBlockLayout, $cursorY, $width, $margin) as $line) {
                $placed[] = $line;
            }
            $cursorY += $textBlockLayout->height;
            if ($i < $lastIndex) {
                $cursorY += $this->gapAfter($textBlockLayout->fontSize);
            }
        }

        return $placed;
    }

    /**
     * Resolve a single block's lines to placed lines starting at the given top
     * edge.
     *
     * @return array<int, LinePosition>
     */
    private function placeBlock(TextBlockLayout $item, int $top, int $width, int $margin): array
    {
        $placed = [];
        foreach ($item->lines as $i => $line) {
            $lineWidth = $this->writer->calculateTextBlockWidth($line, $item->fontSize, $item->fontPath);

            $placed[] = new LinePosition(
                x: $this->resolveX($item->alignment, $lineWidth, $width, $margin),
                y: $top + $item->ascent + $i * $item->lineAdvance,
                text: $line,
                fontSize: $item->fontSize,
                fontPath: $item->fontPath,
                color: $item->color,
            );
        }

        return $placed;
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

    private function resolveX(Alignment $alignment, int $textWidth, int $width, int $margin): int
    {
        return match ($alignment) {
            Alignment::Left => $margin,
            Alignment::Center => intval(($width - $textWidth) / 2),
            Alignment::Right => $width - $textWidth - $margin,
        };
    }

    private function resolveTop(Position $position, int $margin, int $containerHeight, int $contentHeight): int
    {
        return match ($position) {
            Position::Top => $margin,
            Position::Center => intval(($containerHeight - $contentHeight) / 2),
            Position::Bottom => $containerHeight - $margin - $contentHeight,
        };
    }
}
