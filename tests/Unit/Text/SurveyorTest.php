<?php

use Yilanboy\Preview\Text\Enums\Alignment;
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

    $fontPath = $block->fontPath();

    $fontSize = $block->fontSizePixels();

    $width = new Surveyor()->calculateLineWidth('Hello', $fontSize, $fontPath);

    expect($lines[0]->x)->toBe(1200 - $width - 60);
});

it('centers a line horizontally', function () {
    $block = new TextBlock(text: 'Hello', alignment: Alignment::Center);
    $lines = new Surveyor()->place(1200, 630, 60, [$block]);

    $fontPath = $block->fontPath();

    $fontSize = $block->fontSizePixels();

    $width = new Surveyor()->calculateLineWidth('Hello', $fontSize, $fontPath);

    expect($lines[0]->x)->toBe(intval((1200 - $width) / 2));
});

it('places a Top block one ascent below the margin', function () {
    $block = new TextBlock(text: 'Hello', position: Position::Top);
    $lines = new Surveyor()->place(1200, 630, 60, [$block]);

    $fontPath = $block->fontPath();

    $fontSize = $block->fontSizePixels();

    $metrics = new Surveyor()->parseLineMetrics($fontPath);
    $scale = $fontSize / $metrics->unitsPerEm;
    $ascent = (int) round($metrics->ascender * $scale);

    expect($lines[0]->y)->toBe(60 + $ascent);
});

it('stacks the first block above the second when they share a position',
    function (Position $position) {
        $lines = new Surveyor()->place(1200, 630, 60, [
            new TextBlock(text: 'My Blog', position: $position),
            new TextBlock(text: 'A true master is an eternal student',
                position: $position),
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

    $fontSize = $block->fontSizePixels();

    $metrics = new Surveyor()->parseLineMetrics($block->fontPath());
    $scale = $fontSize / $metrics->unitsPerEm;
    $lineHeight = (int) round($metrics->ascender * $scale)
        + (int) round(-$metrics->descender * $scale)
        + (int) round($metrics->lineGap * $scale);
    $advance = (int) round($lineHeight * LineHeight::Loose->multiplier());

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

    expect($lines)
        ->toHaveCount(1)
        ->and($lines[0]->text)
        ->toBe('Hello World')
        ->and($lines[0]->width)
        ->toBe($surveyor->calculateLineWidth('Hello World', 40, $fontPath));
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
        expect($line->text)->toBe(trim($line->text))
            ->and($line->text)->not->toBe('')
            ->and($line->width)->toBe($surveyor->calculateLineWidth($line->text,
                50, $fontPath));
    }
});

it('wraps text when containing manual newlines', function () {
    $surveyor = new Surveyor;
    $fontPath = __DIR__.'/../../../fonts/noto-sans-tc.ttf';

    $lines = $surveyor->wrapText(
        text: "Line 1\nLine 2\n\nLine 4",
        fontSize: 40,
        fontPath: $fontPath,
        maxWidth: 1000,
    );

    expect($lines)->toHaveCount(4)
        ->and($lines[0]->text)->toBe('Line 1')
        ->and($lines[1]->text)->toBe('Line 2')
        ->and($lines[2]->text)->toBe('')
        ->and($lines[3]->text)->toBe('Line 4');
});

it('wraps text when containing CRLF newlines', function () {
    $surveyor = new Surveyor;
    $fontPath = __DIR__.'/../../../fonts/noto-sans-tc.ttf';

    $lines = $surveyor->wrapText(
        text: "Line 1\r\nLine 2\r\n\r\nLine 4",
        fontSize: 40,
        fontPath: $fontPath,
        maxWidth: 1000,
    );

    expect($lines)->toHaveCount(4)
        ->and($lines[0]->text)->toBe('Line 1')
        ->and($lines[1]->text)->toBe('Line 2')
        ->and($lines[2]->text)->toBe('')
        ->and($lines[3]->text)->toBe('Line 4');
});
