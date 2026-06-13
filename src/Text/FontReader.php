<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Text;

use Yilanboy\Preview\Exceptions\RenderFailure;

class FontReader
{
    /**
     * Read a font's declared vertical metrics from its sfnt tables. Cached
     * per font path, since these values are size-independent.
     */
    public function parseLineMetrics(string $fontPath): LineMetrics
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
            $numTables = $this->unpackInts('n', substr((string) fread($handle, 12), 4, 2), $fontPath)[1];
            if ($numTables < 1) {
                throw new RenderFailure("Font has no sfnt tables: {$fontPath}");
            }
            $directory = (string) fread($handle, $numTables * 16);

            $offsets = [];
            for ($i = 0; $i < $numTables; $i++) {
                $record = substr($directory, $i * 16, 16);
                $tag = substr($record, 0, 4);
                if ($tag === 'head' || $tag === 'hhea') {
                    $offsets[$tag] = $this->unpackInts('N', substr($record, 8, 4), $fontPath)[1];
                }
            }

            if (! isset($offsets['head'], $offsets['hhea'])) {
                throw new RenderFailure("Font is missing required head/hhea tables: {$fontPath}");
            }

            // head: unitsPerEm is a uint16 at byte 18.
            fseek($handle, $offsets['head'] + 18);
            $unitsPerEm = $this->unpackInts('n', (string) fread($handle, 2), $fontPath)[1];

            // hhea: ascender, descender, lineGap are three int16 (FWORD) at byte 4.
            fseek($handle, $offsets['hhea'] + 4);
            $values = $this->unpackInts('n3', (string) fread($handle, 6), $fontPath);
            $toSigned = fn (int $v): int => $v >= 0x8000 ? $v - 0x10000 : $v;

            return $cache[$fontPath] = new LineMetrics(
                unitsPerEm: $unitsPerEm,
                ascender: $toSigned($values[1]),
                descender: $toSigned($values[2]),
                lineGap: $toSigned($values[3]),
            );
        } finally {
            fclose($handle);
        }
    }

    /**
     * unpack() with the failure case turned into a RenderFailure, so callers
     * can index into the result directly.
     *
     * @return array<int, int>
     */
    private function unpackInts(string $format, string $data, string $fontPath): array
    {
        $values = unpack($format, $data);
        if ($values === false) {
            throw new RenderFailure("Failed to parse font tables: {$fontPath}");
        }

        /** @var array<int, int> $values */
        return $values;
    }
}
