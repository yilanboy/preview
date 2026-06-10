<?php

use Yilanboy\Preview\Exceptions\InvalidInput;
use Yilanboy\Preview\Text\Enums\Alignment;
use Yilanboy\Preview\Text\Enums\Font;
use Yilanboy\Preview\Text\Enums\FontSize;
use Yilanboy\Preview\Text\Enums\LineHeight;
use Yilanboy\Preview\Text\Enums\Position;
use Yilanboy\Preview\Text\TextBlock;

it('throws when text is empty', function () {
    new TextBlock(text: '');
})->throws(InvalidInput::class, 'TextBlock text cannot be empty');

it('uses sensible defaults when only text is provided', function () {
    $block = new TextBlock(text: 'Hello');

    expect($block->color)->toBe('#030712')
        ->and($block->fontSize)->toBe(FontSize::Medium)
        ->and($block->font)->toBe(Font::NotoSansTC)
        ->and($block->alignment)->toBe(Alignment::Left)
        ->and($block->lineHeight)->toBe(LineHeight::Normal)
        ->and($block->position)->toBe(Position::Center);
});

it('accepts an explicit vertical position', function () {
    $block = new TextBlock(text: 'Hello', position: Position::Bottom);

    expect($block->position)->toBe(Position::Bottom);
});

it('accepts a custom TrueType font path', function () {
    $block = new TextBlock(text: 'Hello', font: Font::NotoSansTC->path());

    expect($block->font)->toBe(Font::NotoSansTC->path());
});

it('throws when the custom font path is not a valid TrueType file', function () {
    new TextBlock(text: 'Hello', font: '/no/such/font.ttf');
})->throws(InvalidInput::class, 'The font path is not a valid TrueType font file');

it('accepts a custom integer font size', function () {
    $block = new TextBlock(text: 'Hello', fontSize: 42);

    expect($block->fontSize)->toBe(42);
});

it('throws when the custom font size is below 1', function (int $fontSize) {
    new TextBlock(text: 'Hello', fontSize: $fontSize);
})->with([
    'zero' => [0],
    'negative' => [-5],
])->throws(InvalidInput::class, 'Font size must be at least 1');

it('accepts a known color name', function () {
    $block = new TextBlock(text: 'Hello', color: 'white');

    expect($block->color)->toBe('white');
});

it('throws when the color is invalid', function () {
    new TextBlock(text: 'Hello', color: 'not-a-color');
})->throws(InvalidInput::class, 'Invalid color: not-a-color');
