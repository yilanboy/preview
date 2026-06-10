<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Text;

use Yilanboy\Preview\Exceptions\RenderFailure;
use Yilanboy\Preview\Text\Enums\Alignment;
use Yilanboy\Preview\Text\Enums\Position;

final readonly class Surveyor
{
    public function __construct(private Tokenizer $tokenizer = new Tokenizer) {}

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
        $fontPath = $block->fontPath();
        $fontSize = $block->fontSizePixels();
        $maxWidth = $width - $margin * 2;

        $lines = $this->wrapText(
            text: $block->text,
            fontSize: $fontSize,
            fontPath: $fontPath,
            maxWidth: $maxWidth,
        );

        // baseline-to-baseline distance (CSS-style line height)
        $lineAdvance = (int) round($fontSize * $block->lineHeight->multiplier());
        // uniform vertical metrics so every line shares the same height
        $metrics = $this->getFontMetrics($fontSize, $fontPath);
        $ascent = $metrics->ascent;
        $blockHeight = $metrics->height() + $lineAdvance * (count($lines) - 1);

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
     * Wrap the text to multiple lines based on the maximum width.
     *
     * @return array<int, MeasuredLine>
     */
    public function wrapText(
        string $text,
        int $fontSize,
        string $fontPath,
        int $maxWidth,
    ): array {
        $lines = [];
        $current = '';
        $currentWidth = null;
        $words = $this->tokenizer->splitStringToArray($text);

        foreach ($words as $word) {
            $proposed = $current.$word;
            $proposedWidth = $this->calculateTextBlockWidth($proposed, $fontSize, $fontPath);

            if ($proposedWidth < $maxWidth) {
                $current = $proposed;
                $currentWidth = $proposedWidth;

                continue;
            }

            $lines[] = $this->finalizeLine($current, $currentWidth, $fontSize, $fontPath);
            $current = $word;
            $currentWidth = null;
        }

        $lines[] = $this->finalizeLine($current, $currentWidth, $fontSize, $fontPath);

        return $lines;
    }

    /**
     * Finalize a wrapped line: trim it, reusing the width already measured
     * during wrapping when trimming changed nothing.
     */
    private function finalizeLine(string $raw, ?int $rawWidth, int $fontSize, string $fontPath): MeasuredLine
    {
        $text = trim($raw);

        $width = $text === $raw && $rawWidth !== null
            ? $rawWidth
            : $this->calculateTextBlockWidth($text, $fontSize, $fontPath);

        return new MeasuredLine($text, $width);
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
        // centering or bottom-aligning, typically a title stacked over a description.
        $groupHeight = 0;
        foreach ($positionGroup as $i => $textBlockLayout) {
            $groupHeight += $textBlockLayout->height;
            if ($i < $lastIndex) {
                $groupHeight += $this->gapAfter($textBlockLayout->fontSize);
            }
        }

        $cursorY = $this->resolveTop($positionGroup[0]->position, $height, $margin, $groupHeight);

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
            $placed[] = new LinePosition(
                x: $this->resolveX($item->alignment, $width, $margin, $line->width),
                y: $top + $item->ascent + $i * $item->lineAdvance,
                text: $line->text,
                fontSize: $item->fontSize,
                fontPath: $item->fontPath,
                color: $item->color,
            );
        }

        return $placed;
    }

    /**
     * Calculate the width of the text image.
     */
    public function calculateTextBlockWidth(
        string $text,
        int $fontSize,
        string $fontPath,
    ): int {
        $boundingBox = imagettfbbox(
            size: $fontSize,
            angle: 0,
            font_filename: $fontPath,
            string: $text,
        );

        if ($boundingBox === false) {
            throw new RenderFailure('Failed to calculate text bounding box');
        }

        return (int) $boundingBox[2] - (int) $boundingBox[0];
    }

    /**
     * Uniform vertical metrics for a font + size, measured from a fixed
     * reference string (independent of the rendered content) so every line
     * shares the same height.
     */
    public function getFontMetrics(int $fontSize, string $fontPath): FontMetrics
    {
        // Reference covers ascenders, descenders, and CJK to capture full extent.
        $bbox = imagettfbbox(
            size: $fontSize,
            angle: 0,
            font_filename: $fontPath,
            string: 'Ag字',
        );

        if ($bbox === false) {
            throw new RenderFailure('Failed to calculate text bounding box');
        }

        return new FontMetrics(
            ascent: (int) -$bbox[7],
            descent: (int) $bbox[1],
        );
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

    private function resolveX(Alignment $alignment, int $containerWidth, int $margin, int $textWidth): int
    {
        return match ($alignment) {
            Alignment::Left => $margin,
            Alignment::Center => intval(($containerWidth - $textWidth) / 2),
            Alignment::Right => $containerWidth - $textWidth - $margin,
        };
    }

    private function resolveTop(Position $position, int $containerHeight, int $margin, int $contentHeight): int
    {
        return match ($position) {
            Position::Top => $margin,
            Position::Center => intval(($containerHeight - $contentHeight) / 2),
            Position::Bottom => $containerHeight - $margin - $contentHeight,
        };
    }
}
