<?php

use Yilanboy\Preview\ColorConverter;

it('can convert hex to rgb array', function () {
    expect(ColorConverter::hexToRgb('#ffffff'))->toBe([255, 255, 255])
        ->and(ColorConverter::hexToRgb('#000000'))->toBe([0, 0, 0]);
});

it('will throw invalid argument exception, if hex format is not correct', function () {
    ColorConverter::hexToRgb('invalid');
})->throws(InvalidArgumentException::class, 'Invalid hex color');

it('can convert color name to hex', function () {
    expect(ColorConverter::nameToHex('white'))->toBe('#ffffff')
        ->and(ColorConverter::nameToHex('black'))->toBe('#000000');
});

it('will throw invalid argument exception, if color name is not correct', function () {
    ColorConverter::nameToHex('invalid');
})->throws(InvalidArgumentException::class, 'Invalid color name');

it('can check string is correct hex format', function () {
    expect(ColorConverter::isValidHex('#ffffff'))->toBeTrue()
        ->and(ColorConverter::isValidHex('#000000'))->toBeTrue()
        ->and(ColorConverter::isValidHex('ffffff'))->toBeFalse()
        ->and(ColorConverter::isValidHex('000000'))->toBeFalse();
});
