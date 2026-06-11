<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Text;

use Yilanboy\Preview\Exceptions\RenderFailure;
use Yilanboy\Preview\Text\Enums\Alignment;
use Yilanboy\Preview\Text\Enums\Position;

final readonly class Surveyor
{
    public function __construct(private Tokenizer $tokenizer = new Tokenizer
    ) {}

    /**
     * Resolve blocks to their placed lines on a canvas of the given size.
     *
     * Blocks sharing a Position are stacked in input order (e.g. title above
     * description) and anchored as a single group, so they never overlap.
     *
     * @param  array<int, TextBlock>  $blocks
     * @return array<int, PositionedLine>
     */
    public function place(int $width, int $height, int $margin, array $blocks): array
    {
        /* @var array<string<'TOP', 'CENTER', 'BOTTOM'>, array<MeasuredBlock>> $positionGroups */
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
    private function measure(TextBlock $block, int $width, int $margin): MeasuredBlock
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

        // uniform vertical metrics so every line shares the same height
        $metrics = $this->getFontMetrics($fontSize, $fontPath);
        $ascent = $metrics->ascent;
        // baseline-to-baseline distance: the font's natural line box scaled by
        // the chosen multiplier. A multiplier of 1.0 reproduces single-line
        // spacing, so adjacent lines never overlap whatever the multiplier or font.
        $lineAdvance = (int) round($metrics->lineHeight() * $block->lineHeight->multiplier());
        $blockHeight = $metrics->height() + $lineAdvance * (count($lines) - 1);

        return new MeasuredBlock(
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
        $words = $this->tokenizer->tokenize($text);

        foreach ($words as $word) {
            if ($word === "\n") {
                $lines[] = $this->finalizeLine($current, $currentWidth, $fontSize, $fontPath);
                $current = '';
                $currentWidth = null;

                continue;
            }

            $proposed = $current.$word;
            $proposedWidth = $this->calculateLineWidth($proposed, $fontSize, $fontPath);

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
            : $this->calculateLineWidth($text, $fontSize, $fontPath);

        return new MeasuredLine($text, $width);
    }

    /**
     * Anchor a group of stacked blocks at their shared position and place them
     * top to bottom, separated by the gap below each block.
     *
     * @param  array<int, MeasuredBlock>  $positionGroup
     * @return array<int, PositionedLine>
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
     * @return array<int, PositionedLine>
     */
    private function placeBlock(MeasuredBlock $item, int $top, int $width, int $margin): array
    {
        $placed = [];
        foreach ($item->lines as $i => $line) {
            $placed[] = new PositionedLine(
                x: $this->resolveX(
                    $item->alignment,
                    $width,
                    $margin,
                    $line->width
                ),
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
    public function calculateLineWidth(
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
     * Uniform vertical metrics for a font + size, read from the font's own
     * declared line box (its `head` and `hhea` tables) rather than probed from
     * glyphs. This is independent of the rendered content, so every line shares
     * the same height, and it is unaffected by missing glyphs — e.g. CJK
     * characters set in a Latin-only font no longer inflate the line height.
     */
    public function getFontMetrics(int $fontSize, string $fontPath): FontMetrics
    {
        [$unitsPerEm, $ascender, $descender, $lineGap] = $this->parseLineMetrics($fontPath);
        $scale = $fontSize / $unitsPerEm;

        return new FontMetrics(
            ascent: (int) round($ascender * $scale),
            descent: (int) round(-$descender * $scale),
            lineGap: (int) round($lineGap * $scale),
        );
    }

    /**
     * Read a font's declared vertical metrics from its sfnt tables, returning
     * [unitsPerEm, ascender, descender, lineGap] in font design units. The
     * descender is negative (it sits below the baseline). Cached per font path,
     * since these values are size-independent.
     *
     * @return array{int, int, int, int}
     */
    private function parseLineMetrics(string $fontPath): array
    {
        static $cache = [];

        if (isset($cache[$fontPath])) {
            return $cache[$fontPath];
        }

        $handle = fopen($fontPath, 'rb');
        if ($handle === false) {
            throw new RenderFailure("Failed to open font file: {$fontPath}");
        }

        try {
            // Offset table: numTables is a uint16 at byte 4. Each of the
            // following 16-byte records carries a 4-char tag and the table's
            // absolute offset (uint32 at byte 8 of the record).
            $numTables = unpack('n', substr((string) fread($handle, 12), 4, 2))[1];
            $directory = (string) fread($handle, $numTables * 16);

            $offsets = [];
            for ($i = 0; $i < $numTables; $i++) {
                $record = substr($directory, $i * 16, 16);
                $tag = substr($record, 0, 4);
                if ($tag === 'head' || $tag === 'hhea') {
                    $offsets[$tag] = unpack('N', substr($record, 8, 4))[1];
                }
            }

            if (! isset($offsets['head'], $offsets['hhea'])) {
                throw new RenderFailure("Font is missing required head/hhea tables: {$fontPath}");
            }

            // head: unitsPerEm is a uint16 at byte 18.
            fseek($handle, $offsets['head'] + 18);
            $unitsPerEm = unpack('n', (string) fread($handle, 2))[1];

            // hhea: ascender, descender, lineGap are three int16 (FWORD) at byte 4.
            fseek($handle, $offsets['hhea'] + 4);
            $values = unpack('n3', (string) fread($handle, 6));
            $toSigned = fn (int $v): int => $v >= 0x8000 ? $v - 0x10000 : $v;

            return $cache[$fontPath] = [
                $unitsPerEm,
                $toSigned($values[1]),
                $toSigned($values[2]),
                $toSigned($values[3]),
            ];
        } finally {
            fclose($handle);
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
