<?php

use Yilanboy\Preview\ColorConverter;
use Yilanboy\Preview\Exceptions\InvalidInput;

it('can convert hex to rgb array', function () {
    expect(ColorConverter::hexToRgb('#ffffff'))->toBe([255, 255, 255])
        ->and(ColorConverter::hexToRgb('#000000'))->toBe([0, 0, 0]);
});

it('will throw invalid argument exception, if hex format is not correct', function () {
    ColorConverter::hexToRgb('invalid');
})->throws(InvalidInput::class, 'Invalid hex color');

it('can convert color name to hex', function () {
    expect(ColorConverter::nameToHex('white'))->toBe('#ffffff')
        ->and(ColorConverter::nameToHex('black'))->toBe('#000000');
});

it('will throw invalid argument exception, if color name is not correct', function () {
    ColorConverter::nameToHex('invalid');
})->throws(InvalidInput::class, 'Invalid color name');

it('can check string is correct hex format', function () {
    expect(ColorConverter::isValidHex('#ffffff'))->toBeTrue()
        ->and(ColorConverter::isValidHex('#000000'))->toBeTrue()
        ->and(ColorConverter::isValidHex('ffffff'))->toBeFalse()
        ->and(ColorConverter::isValidHex('000000'))->toBeFalse();
});

it('accepts a valid hex code as a color', function () {
    expect(ColorConverter::isValidColor('#10b981'))->toBeTrue()
        ->and(ColorConverter::isValidColor('#000000'))->toBeTrue();
});

it('accepts a known color name as a color, case-insensitively', function () {
    expect(ColorConverter::isValidColor('white'))->toBeTrue()
        ->and(ColorConverter::isValidColor('BLACK'))->toBeTrue()
        ->and(ColorConverter::isValidColor('Red'))->toBeTrue();
});

it('rejects an unknown name or malformed hex as a color', function () {
    expect(ColorConverter::isValidColor('chartreuse'))->toBeFalse()
        ->and(ColorConverter::isValidColor('ffffff'))->toBeFalse()
        ->and(ColorConverter::isValidColor('#fff'))->toBeFalse()
        ->and(ColorConverter::isValidColor(''))->toBeFalse();
});
