<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Image;

use GdImage;
use Yilanboy\Preview\Color\Converter;

final class Builder
{
    private const string DEFAULT_FONT_PATH = __DIR__.'/../../fonts/noto-sans-tc.ttf';

    private const float MARGIN_RATIO = 0.05;

    public int $width = 1200;

    public int $height = 600;

    public array $header = [
        'text'      => '',
        'font_path' => self::DEFAULT_FONT_PATH,
        'font_size' => 50,
        'color'     => '#030712',
    ];

    public array $title = [
        'text'      => '',
        'font_path' => self::DEFAULT_FONT_PATH,
        'font_size' => 50,
        'color'     => '#030712',
    ];

    public string $backgroundColor = '#f9fafb';

    public GdImage $image;

    public function __construct(
        public Converter $converter = new Converter(),
        public Writer $writer = new Writer()
    ) {
    }

    public function size(int $width, int $height): Builder
    {
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    public function backgroundColor(string $color): Builder
    {
        $this->backgroundColor = $this->converter->toHex($color);

        return $this;
    }

    public function title(
        string $text,
        ?string $color = null,
        ?int $fontSize = null,
        ?string $fontPath = null,
    ): Builder {
        $this->updateTextBlock($this->title, $text, $color, $fontSize, $fontPath);

        return $this;
    }

    public function header(
        string $text,
        ?string $color = null,
        ?int $fontSize = null,
        ?string $fontPath = null,
    ): Builder {
        $this->updateTextBlock($this->header, $text, $color, $fontSize, $fontPath);

        return $this;
    }

    private function updateTextBlock(
        array &$block,
        string $text,
        ?string $color,
        ?int $fontSize,
        ?string $fontPath,
    ): void {
        $block['text'] = $text;

        if ($color !== null) {
            $block['color'] = $this->converter->toHex($color);
        }

        if ($fontSize !== null) {
            $block['font_size'] = $fontSize;
        }

        if ($fontPath !== null) {
            $block['font_path'] = $fontPath;
        }
    }

    private function configureCanvas(): void
    {
        $this->image = imagecreatetruecolor($this->width, $this->height);
        imagefill($this->image, 0, 0, $this->allocateColor($this->backgroundColor));
    }

    /**
     * @param  callable(array, int): int  $resolveY  receives the wrapped-text bbox and pixel height; returns the y baseline.
     */
    private function drawTextBlock(array $block, callable $resolveY): void
    {
        if ($block['text'] === '') {
            return;
        }

        $wrappedText = $this->writer->wrapTextImage(
            text: $block['text'],
            fontSize: $block['font_size'],
            fontPath: $block['font_path'],
            maxWidth: intval($this->width - $this->width * self::MARGIN_RATIO * 2)
        );

        $bbox = imagettfbbox(
            $block['font_size'], 0, $block['font_path'], $wrappedText);

        $textHeight = $bbox[1] - $bbox[5];

        imagettftext(
            image: $this->image,
            size: $block['font_size'],
            angle: 0,
            x: intval($this->width * self::MARGIN_RATIO),
            y: $resolveY($bbox, $textHeight),
            color: $this->allocateColor($block['color']),
            font_filename: $block['font_path'],
            text: $wrappedText
        );
    }

    private function allocateColor(string $hex): int
    {
        return imagecolorallocate($this->image, ...$this->converter->hexToRgb($hex));
    }

    private function render(): void
    {
        $this->configureCanvas();

        $this->drawTextBlock(
            $this->header,
            fn(array $bbox, int $textHeight): int => intval(imagesy($this->image) / 3 - $textHeight / 2),
        );

        $this->drawTextBlock(
            $this->title,
            fn(array $bbox, int $textHeight): int => intval((imagesy($this->image) - $textHeight) / 2 - $bbox[5]),
        );
    }

    public function output(): void
    {
        $this->render();

        header('Content-Type: image/png');
        imagepng($this->image);
    }

    public function save(string $path): void
    {
        $this->render();

        imagepng($this->image, $path);
    }
}
