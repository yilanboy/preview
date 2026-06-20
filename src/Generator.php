<?php

declare(strict_types=1);

namespace Yilanboy\Preview;

use GdImage;
use Yilanboy\Preview\Canvas\Background;
use Yilanboy\Preview\Canvas\Enums\Format;
use Yilanboy\Preview\Canvas\Enums\Margin;
use Yilanboy\Preview\Canvas\Enums\Size;
use Yilanboy\Preview\Canvas\Solid;
use Yilanboy\Preview\Exceptions\InvalidInput;
use Yilanboy\Preview\Exceptions\RenderFailure;
use Yilanboy\Preview\Text\Surveyor;
use Yilanboy\Preview\Text\TextBlock;
use Yilanboy\Preview\Text\Writer;

final class Generator
{
    /** @var positive-int */
    private int $width;

    /** @var positive-int */
    private int $height;

    private Margin $margin = Margin::Medium;

    private ?TextBlock $title = null;

    private ?TextBlock $description = null;

    private Format $format = Format::PNG;

    public function __construct(
        private Background $background = new Solid(color: '#f9fafb'),
        private readonly Surveyor $surveyor = new Surveyor,
        private readonly Writer $writer = new Writer
    ) {
        $this->size(Size::OpenGraph);
    }

    public function size(Size $size): self
    {
        return $this->dimensions($size->width(), $size->height());
    }

    public function dimensions(int $width, int $height): self
    {
        if ($width < 1 || $height < 1) {
            throw new InvalidInput('Width and height must be at least 1');
        }

        $this->width = $width;
        $this->height = $height;

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

    public function bytes(): string
    {
        ob_start();
        $image = $this->render();
        $this->format->write($image);
        $imageData = ob_get_clean();

        if ($imageData === false) {
            throw new RenderFailure('Failed to get image data');
        }

        return $imageData;
    }

    private function render(): GdImage
    {
        $image = imagecreatetruecolor($this->width, $this->height);

        if ($image === false) {
            throw new RenderFailure('Failed to create image canvas');
        }

        $this->background->draw($image, $this->width, $this->height);

        // remove empty text blocks
        $blocks = array_filter([$this->title, $this->description]);

        $lines = $this->surveyor->place($this->width, $this->height,
            $this->margin->value, $blocks);

        foreach ($lines as $line) {
            $this->writer->stamp($image, $line);
        }

        return $image;
    }
}
