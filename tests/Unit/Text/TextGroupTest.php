<?php

use Yilanboy\Preview\Text\Enums\Alignment;
use Yilanboy\Preview\Text\Enums\Font;
use Yilanboy\Preview\Text\Enums\LineHeight;
use Yilanboy\Preview\Text\Enums\Position;
use Yilanboy\Preview\Text\TextBlock;
use Yilanboy\Preview\Text\TextBlockGroup;
use Yilanboy\Preview\Text\Writer;

it('anchors a left-aligned block at the margin', function () {
    $lines = new TextBlockGroup()->place(1200, 630, 60, [
        new TextBlock(text: 'Hello', alignment: Alignment::Left),
    ]);

    expect($lines)->toHaveCount(1)
        ->and($lines[0]->x)->toBe(60);
});

it('right-aligns a line against the far margin', function () {
    $block = new TextBlock(text: 'Hello', alignment: Alignment::Right);
    $lines = new TextBlockGroup()->place(1200, 630, 60, [$block]);

    $fontPath = $block->font instanceof Font ? $block->font->path() : $block->font;

    $width = new Writer()->calculateTextBlockWidth('Hello', $block->fontSize->value, $fontPath);

    expect($lines[0]->x)->toBe(1200 - $width - 60);
});

it('centers a line horizontally', function () {
    $block = new TextBlock(text: 'Hello', alignment: Alignment::Center);
    $lines = new TextBlockGroup()->place(1200, 630, 60, [$block]);

    $fontPath = $block->font instanceof Font ? $block->font->path() : $block->font;

    $width = new Writer()->calculateTextBlockWidth('Hello', $block->fontSize->value, $fontPath);

    expect($lines[0]->x)->toBe(intval((1200 - $width) / 2));
});

it('places a Top block one ascent below the margin', function () {
    $block = new TextBlock(text: 'Hello', position: Position::Top);
    $lines = new TextBlockGroup()->place(1200, 630, 60, [$block]);

    $fontPath = $block->font instanceof Font ? $block->font->path() : $block->font;

    $boundingBox = new Writer()->lineBoundingBox($block->fontSize->value, $fontPath);
    $ascent = -$boundingBox[7];  // top of glyph above baseline (bbox[7] is negative)

    expect($lines[0]->y)->toBe(60 + $ascent);
});

it('stacks the first block above the second when they share a position', function (Position $position) {
    $lines = new TextBlockGroup()->place(1200, 630, 60, [
        new TextBlock(text: 'My Blog', position: $position),
        new TextBlock(text: 'A true master is an eternal student', position: $position),
    ]);

    // Lines are emitted top to bottom: [0] is the single-line title, [1] is the
    // topmost description line. The title sits entirely above the description.
    expect(count($lines))->toBeGreaterThanOrEqual(2)
        ->and($lines[0]->y)->toBeLessThan($lines[1]->y);
})->with([Position::Top, Position::Center, Position::Bottom]);

it('steps each wrapped line down by the line advance', function () {
    $block = new TextBlock(
        text: 'The quick brown fox jumps over the lazy dog while the early bird catches the worm and a stitch in time saves nine',
        lineHeight: LineHeight::Loose,
    );
    $lines = new TextBlockGroup()->place(1200, 630, 60, [$block]);

    expect(count($lines))->toBeGreaterThan(1);

    $advance = (int) round($block->fontSize->value * LineHeight::Loose->multiplier());

    for ($i = 1; $i < count($lines); $i++) {
        expect($lines[$i]->y - $lines[$i - 1]->y)->toBe($advance);
    }
});

it('passes the block color through untouched', function () {
    $lines = new TextBlockGroup()->place(1200, 630, 60, [
        new TextBlock(text: 'Hi', color: 'white'),
    ]);

    expect($lines[0]->color)->toBe('white');
});

it('returns no lines when given no blocks', function () {
    expect(new TextBlockGroup()->place(1200, 630, 60, []))->toBe([]);
});
