<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Text;

use InvalidArgumentException;
use Yilanboy\Preview\Text\Enums\Alignment;
use Yilanboy\Preview\Text\Enums\Font;
use Yilanboy\Preview\Text\Enums\FontSize;
use Yilanboy\Preview\Text\Enums\LineHeight;
use Yilanboy\Preview\Text\Enums\Position;

final readonly class TextBlock
{
    public function __construct(
        public string $text,
        public string $color = '#030712',
        public FontSize $fontSize = FontSize::Medium,
        public Font $font = Font::NotoSansTC,
        public Alignment $alignment = Alignment::Left,
        public LineHeight $lineHeight = LineHeight::Normal,
        public Position $position = Position::Center,
    ) {
        if ($text === '') {
            throw new InvalidArgumentException('TextBlock text cannot be empty');
        }
    }
}
