<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Image;

use InvalidArgumentException;
use Yilanboy\Preview\Image\Enums\Alignment;
use Yilanboy\Preview\Image\Enums\Font;
use Yilanboy\Preview\Image\Enums\FontSize;

final readonly class TextBlock
{
    public function __construct(
        public string $text,
        public string $color = '#030712',
        public FontSize $fontSize = FontSize::Medium,
        public Font $font = Font::NotoSansTC,
        public Alignment $alignment = Alignment::Left,
    ) {
        if ($text === '') {
            throw new InvalidArgumentException('TextBlock text cannot be empty');
        }
    }

    public function withText(string $text): self
    {
        return new self($text, $this->color, $this->fontSize, $this->font, $this->alignment);
    }

    public function withColor(string $color): self
    {
        return new self($this->text, $color, $this->fontSize, $this->font, $this->alignment);
    }

    public function withFontSize(FontSize $fontSize): self
    {
        return new self($this->text, $this->color, $fontSize, $this->font, $this->alignment);
    }

    public function withFont(Font $font): self
    {
        return new self($this->text, $this->color, $this->fontSize, $font, $this->alignment);
    }

    public function withAlignment(Alignment $alignment): self
    {
        return new self($this->text, $this->color, $this->fontSize, $this->font, $alignment);
    }
}
