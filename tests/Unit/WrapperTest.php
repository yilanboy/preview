<?php

use Yilanboy\Preview\Text\Writer;

it('can wrap the english sentence into words', function () {
    $writer = new Writer;

    expect($writer->splitStringToArray('Hello World!'))
        ->toBe(['Hello', ' ', 'World', '!']);
});

it('can wrap the chinese sentence into character', function () {
    $writer = new Writer;

    expect($writer->splitStringToArray('你好世界！'))
        ->toBe(['你', '好', '世', '界', '！']);
});

it('can wrap sentence that mix english and chinese', function () {
    $writer = new Writer;

    expect($writer->splitStringToArray('Hello 世界！'))
        ->toBe(['Hello', ' ', '世', '界', '！']);
});

it('returns a single line when text fits in the max width', function () {
    $writer = new Writer;
    $fontPath = __DIR__.'/../../fonts/noto-sans-tc.ttf';

    $lines = $writer->wrapText(
        text: 'Hello World',
        fontSize: 40,
        fontPath: $fontPath,
        maxWidth: 1000,
    );

    expect($lines)->toBe(['Hello World']);
});

it('splits long text into multiple trimmed lines', function () {
    $writer = new Writer;
    $fontPath = __DIR__.'/../../fonts/noto-sans-tc.ttf';

    $lines = $writer->wrapText(
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
