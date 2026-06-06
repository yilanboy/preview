<?php

use Yilanboy\Preview\Text\Enums\Alignment;
use Yilanboy\Preview\Text\Enums\Font;
use Yilanboy\Preview\Text\Enums\FontSize;
use Yilanboy\Preview\Text\Enums\LineHeight;
use Yilanboy\Preview\Text\Enums\Position;
use Yilanboy\Preview\Text\TextBlock;

it('throws when text is empty', function () {
    new TextBlock(text: '');
})->throws(InvalidArgumentException::class, 'TextBlock text cannot be empty');

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
