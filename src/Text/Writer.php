<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Text;

use RuntimeException;

final class Writer
{
    /**
     * Splits the string into words.
     * This method will split English into words,
     * and Chinese and special symbols into characters.
     *
     * @return array<string>
     */
    public function splitStringToArray(string $input): array
    {
        preg_match_all('/\p{Han}|[a-zA-Z0-9]+|\s|[^\p{Han}\s\w]/u', $input, $matches);

        return $matches[0];
    }

    /**
     * Calculate the width of the text image.
     */
    public function calculateTextImageWidth(
        string $text,
        int $fontSize,
        string $fontPath,
    ): int {
        $bbox = imagettfbbox(
            size: $fontSize,
            angle: 0,
            font_filename: $fontPath,
            string: $text,
        );

        if ($bbox === false) {
            throw new RuntimeException('Failed to calculate text bounding box');
        }

        return $bbox[2] - $bbox[0];
    }

    /**
     * Uniform vertical metrics for a font + size, measured from a fixed
     * reference string (independent of the rendered content) so every line
     * shares the same height.
     *
     * @return array{ascent: int, height: int}
     */
    public function lineMetrics(int $fontSize, string $fontPath): array
    {
        // Reference covers ascenders, descenders, and CJK to capture full extent.
        $bbox = imagettfbbox(
            size: $fontSize,
            angle: 0,
            font_filename: $fontPath,
            string: 'Ag字',
        );

        if ($bbox === false) {
            throw new RuntimeException('Failed to calculate text bounding box');
        }

        $ascent = -$bbox[7];  // top of glyph above baseline (bbox[7] is negative)
        $descent = $bbox[1];  // below baseline

        return ['ascent' => $ascent, 'height' => $ascent + $descent];
    }

    /**
     * Wrap the text to multiple lines based on the maximum width.
     *
     * @return array<int, string>
     */
    public function wrapText(
        string $text,
        int $fontSize,
        string $fontPath,
        int $maxWidth,
    ): array {
        $lines = [];
        $current = '';
        $words = $this->splitStringToArray($text);

        foreach ($words as $word) {
            $proposed = $current.$word;

            if ($this->calculateTextImageWidth($proposed, $fontSize, $fontPath) < $maxWidth) {
                $current = $proposed;

                continue;
            }

            $lines[] = trim($current);
            $current = $word;
        }

        $lines[] = trim($current);

        return $lines;
    }
}
