<?php

use Yilanboy\Preview\ColorConverter;

it('can convert hex to rgb array', function () {
    $converter = new ColorConverter;

    expect($converter->hexToRgb('#ffffff'))->toBe([255, 255, 255])
        ->and($converter->hexToRgb('#000000'))->toBe([0, 0, 0]);
});

it('will throw invalid argument exception, if hex format is not correct', function () {
    $converter = new ColorConverter;

    $converter->hexToRgb('invalid');
})->throws(InvalidArgumentException::class, 'Invalid hex color');

it('can convert color name to hex', function () {
    $converter = new ColorConverter;

    expect($converter->nameToHex('white'))->toBe('#ffffff')
        ->and($converter->nameToHex('black'))->toBe('#000000');
});

it('will throw invalid argument exception, if color name is not correct', function () {
    $converter = new ColorConverter;

    $converter->nameToHex('invalid');
})->throws(InvalidArgumentException::class, 'Invalid color name');

it('can check string is correct hex format', function () {
    $converter = new ColorConverter;

    expect($converter->isHexColor('#ffffff'))->toBeTrue()
        ->and($converter->isHexColor('#000000'))->toBeTrue()
        ->and($converter->isHexColor('ffffff'))->toBeFalse()
        ->and($converter->isHexColor('000000'))->toBeFalse();
});
