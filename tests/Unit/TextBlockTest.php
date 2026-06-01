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

it('is immutable when modified', function () {
    $original = new TextBlock(text: 'Hello', color: 'red');
    $modified = $original->withColor('blue');

    expect($original->color)->toBe('red')
        ->and($modified->color)->toBe('blue')
        ->and($original)->not->toBe($modified);
});

it('uses sensible defaults when only text is provided', function () {
    $block = new TextBlock(text: 'Hello');

    expect($block->color)->toBe('#030712')
        ->and($block->fontSize)->toBe(FontSize::Medium)
        ->and($block->font)->toBe(Font::NotoSansTC)
        ->and($block->alignment)->toBe(Alignment::Left)
        ->and($block->lineHeight)->toBe(LineHeight::Normal)
        ->and($block->position)->toBeNull();
});

it('returns a new instance with updated line height', function () {
    $original = new TextBlock(text: 'Hello');
    $modified = $original->withLineHeight(LineHeight::Loose);

    expect($original->lineHeight)->toBe(LineHeight::Normal)
        ->and($modified->lineHeight)->toBe(LineHeight::Loose)
        ->and($original)->not->toBe($modified);
});

it('accepts an explicit vertical position', function () {
    $block = new TextBlock(text: 'Hello', position: Position::Bottom);

    expect($block->position)->toBe(Position::Bottom);
});

it('returns a new instance with updated position', function () {
    $original = new TextBlock(text: 'Hello', position: Position::Top);
    $modified = $original->withPosition(Position::Bottom);

    expect($original->position)->toBe(Position::Top)
        ->and($modified->position)->toBe(Position::Bottom)
        ->and($original)->not->toBe($modified);
});
