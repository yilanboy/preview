<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Text;

use Yilanboy\Preview\ColorConverter;
use Yilanboy\Preview\Exceptions\InvalidInput;
use Yilanboy\Preview\Text\Enums\Alignment;
use Yilanboy\Preview\Text\Enums\Font;
use Yilanboy\Preview\Text\Enums\FontSize;
use Yilanboy\Preview\Text\Enums\LineHeight;
use Yilanboy\Preview\Text\Enums\Position;

final readonly class TextBlock
{
    /**
     * @param  FontSize|int  $fontSize  A FontSize preset, or a custom size in
     *                                  pixels. Must be at least 1.
     * @param  Font|string  $font  A bundled Font, or a path to a custom font
     *                             file. Only TrueType (.ttf) files are
     *                             supported; OpenType (.otf) is rejected. The
     *                             path is trusted: it must come from the
     *                             developer, never from unsanitised end-user
     *                             input, as it is read straight off disk.
     */
    public function __construct(
        public string $text,
        public string $color = '#030712',
        public FontSize|int $fontSize = FontSize::Medium,
        public Font|string $font = Font::NotoSansTC,
        public Alignment $alignment = Alignment::Left,
        public LineHeight $lineHeight = LineHeight::Normal,
        public Position $position = Position::Center,
    ) {
        if ($text === '') {
            throw new InvalidInput('TextBlock text cannot be empty');
        }

        if (! ColorConverter::isValidColor($color)) {
            throw new InvalidInput("Invalid color: {$color}");
        }

        if (is_int($fontSize) && $fontSize < 1) {
            throw new InvalidInput('Font size must be at least 1');
        }

        if (! $font instanceof Font && ! FontValidator::isValidTtf($font)) {
            throw new InvalidInput('The font path is not a valid TrueType font file');
        }
    }

    public function fontSizePixels(): int
    {
        return $this->fontSize instanceof FontSize ? $this->fontSize->value : $this->fontSize;
    }

    public function fontPath(): string
    {
        return $this->font instanceof Font ? $this->font->path() : $this->font;
    }
}
