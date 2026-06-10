<?php

use Yilanboy\Preview\Canvas\Background\Solid;
use Yilanboy\Preview\Exceptions\InvalidInput;

it('stores the color verbatim', function () {
    $solid = new Solid('#10b981');

    expect($solid->color)->toBe('#10b981');
});

it('accepts a known color name', function () {
    $solid = new Solid('white');

    expect($solid->color)->toBe('white');
});

it('throws when the color is invalid', function () {
    new Solid('not-a-color');
})->throws(InvalidInput::class, 'Invalid color: not-a-color');
