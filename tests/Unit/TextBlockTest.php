<?php

use Yilanboy\Preview\Image\Enums\Alignment;
use Yilanboy\Preview\Image\Enums\Font;
use Yilanboy\Preview\Image\Enums\FontSize;
use Yilanboy\Preview\Image\TextBlock;

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
        ->and($block->alignment)->toBe(Alignment::Left);
});
