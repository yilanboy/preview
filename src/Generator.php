<?php

declare(strict_types=1);

namespace Yilanboy\Preview;

use GdImage;
use RuntimeException;
use Yilanboy\Preview\Canvas\Background\Solid;
use Yilanboy\Preview\Canvas\Enums\Format;
use Yilanboy\Preview\Canvas\Enums\Margin;
use Yilanboy\Preview\Canvas\Enums\Size;
use Yilanboy\Preview\Contracts\Background;
use Yilanboy\Preview\Text\PlacedLine;
use Yilanboy\Preview\Text\TextBlock;
use Yilanboy\Preview\Text\TextGroup;

final class Generator
{
    private int $width;

    private int $height;

    private Margin $margin = Margin::Medium;

    private ?TextBlock $title = null;

    private ?TextBlock $description = null;

    private Format $format = Format::PNG;

    public function __construct(
        private Background $background = new Solid(color: '#f9fafb'),
        private readonly ColorConverter $converter = new ColorConverter,
        private readonly TextGroup $group = new TextGroup,
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

    public function format(Format $format): self
    {
        $this->format = $format;

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
        header('Content-Type: '.$this->format->mimeType());
        $this->format->write($image);
    }

    public function save(string $path): void
    {
        $image = $this->render();
        $this->format->write($image, $path);
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

        // remove empty text blocks
        $blocks = array_filter([$this->title, $this->description]);

        // lines 是包含 title 與 description 每一行的資訊，即 PlacedLine
        $lines = $this->group->place($this->width, $this->height, $this->margin->value, $blocks);

        foreach ($lines as $line) {
            $this->stamp($image, $line);
        }

        return $image;
    }

    /**
     * Draw a single placed line onto the canvas at its resolved baseline.
     */
    private function stamp(GdImage $image, PlacedLine $line): void
    {
        imagettftext(
            image: $image,
            size: $line->fontSize,
            angle: 0,
            x: $line->x,
            y: $line->y,
            color: $this->allocateColor($image, $this->converter->toHex($line->color)),
            font_filename: $line->fontPath,
            text: $line->text,
        );
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
