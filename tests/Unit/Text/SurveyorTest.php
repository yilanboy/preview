<?php

use Yilanboy\Preview\Text\Enums\Alignment;
use Yilanboy\Preview\Text\Enums\Font;
use Yilanboy\Preview\Text\Enums\LineHeight;
use Yilanboy\Preview\Text\Enums\Position;
use Yilanboy\Preview\Text\Surveyor;
use Yilanboy\Preview\Text\TextBlock;

it('anchors a left-aligned block at the margin', function () {
    $lines = new Surveyor()->place(1200, 630, 60, [
        new TextBlock(text: 'Hello', alignment: Alignment::Left),
    ]);

    expect($lines)->toHaveCount(1)
        ->and($lines[0]->x)->toBe(60);
});

it('right-aligns a line against the far margin', function () {
    $block = new TextBlock(text: 'Hello', alignment: Alignment::Right);
    $lines = new Surveyor()->place(1200, 630, 60, [$block]);

    $fontPath = $block->font instanceof Font ? $block->font->path() : $block->font;

    $width = new Surveyor()->calculateTextBlockWidth('Hello', $block->fontSize->value, $fontPath);

    expect($lines[0]->x)->toBe(1200 - $width - 60);
});

it('centers a line horizontally', function () {
    $block = new TextBlock(text: 'Hello', alignment: Alignment::Center);
    $lines = new Surveyor()->place(1200, 630, 60, [$block]);

    $fontPath = $block->font instanceof Font ? $block->font->path() : $block->font;

    $width = new Surveyor()->calculateTextBlockWidth('Hello', $block->fontSize->value, $fontPath);

    expect($lines[0]->x)->toBe(intval((1200 - $width) / 2));
});

it('places a Top block one ascent below the margin', function () {
    $block = new TextBlock(text: 'Hello', position: Position::Top);
    $lines = new Surveyor()->place(1200, 630, 60, [$block]);

    $fontPath = $block->font instanceof Font ? $block->font->path() : $block->font;

    $metrics = new Surveyor()->getFontMetrics($block->fontSize->value, $fontPath);
    $ascent = $metrics->ascent;

    expect($lines[0]->y)->toBe(60 + $ascent);
});

it('stacks the first block above the second when they share a position', function (Position $position) {
    $lines = new Surveyor()->place(1200, 630, 60, [
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
    $lines = new Surveyor()->place(1200, 630, 60, [$block]);

    expect(count($lines))->toBeGreaterThan(1);

    $advance = (int) round($block->fontSize->value * LineHeight::Loose->multiplier());

    for ($i = 1; $i < count($lines); $i++) {
        expect($lines[$i]->y - $lines[$i - 1]->y)->toBe($advance);
    }
});

it('passes the block color through untouched', function () {
    $lines = new Surveyor()->place(1200, 630, 60, [
        new TextBlock(text: 'Hi', color: 'white'),
    ]);

    expect($lines[0]->color)->toBe('white');
});

it('returns no lines when given no blocks', function () {
    expect(new Surveyor()->place(1200, 630, 60, []))->toBe([]);
});

it('returns a single line when text fits in the max width', function () {
    $surveyor = new Surveyor;
    $fontPath = __DIR__.'/../../../fonts/noto-sans-tc.ttf';

    $lines = $surveyor->wrapText(
        text: 'Hello World',
        fontSize: 40,
        fontPath: $fontPath,
        maxWidth: 1000,
    );

    expect($lines)->toBe(['Hello World']);
});

it('splits long text into multiple trimmed lines', function () {
    $surveyor = new Surveyor;
    $fontPath = __DIR__.'/../../../fonts/noto-sans-tc.ttf';

    $lines = $surveyor->wrapText(
        text: 'The quick brown fox jumps over the lazy dog while the early bird catches the worm',
        fontSize: 50,
        fontPath: $fontPath,
        maxWidth: 600,
    );

    expect($lines)->toBeArray()
        ->and(count($lines))->toBeGreaterThan(1);

    foreach ($lines as $line) {
        expect($line)->toBe(trim($line))
            ->and($line)->not->toBe('');
    }
});
