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
        public ?Position $position = null,
    ) {
        if ($text === '') {
            throw new InvalidArgumentException('TextBlock text cannot be empty');
        }
    }

    public function withText(string $text): self
    {
        return new self($text, $this->color, $this->fontSize, $this->font, $this->alignment, $this->lineHeight, $this->position);
    }

    public function withColor(string $color): self
    {
        return new self($this->text, $color, $this->fontSize, $this->font, $this->alignment, $this->lineHeight, $this->position);
    }

    public function withFontSize(FontSize $fontSize): self
    {
        return new self($this->text, $this->color, $fontSize, $this->font, $this->alignment, $this->lineHeight, $this->position);
    }

    public function withFont(Font $font): self
    {
        return new self($this->text, $this->color, $this->fontSize, $font, $this->alignment, $this->lineHeight, $this->position);
    }

    public function withAlignment(Alignment $alignment): self
    {
        return new self($this->text, $this->color, $this->fontSize, $this->font, $alignment, $this->lineHeight, $this->position);
    }

    public function withLineHeight(LineHeight $lineHeight): self
    {
        return new self($this->text, $this->color, $this->fontSize, $this->font, $this->alignment, $lineHeight, $this->position);
    }

    public function withPosition(?Position $position): self
    {
        return new self($this->text, $this->color, $this->fontSize, $this->font, $this->alignment, $this->lineHeight, $position);
    }
}
