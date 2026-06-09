<?php

use Yilanboy\Preview\Canvas\Background\Gradient;
use Yilanboy\Preview\Canvas\Enums\GradientDirection;

it('defaults to vertical direction', function () {
    $gradient = new Gradient('#10b981', '#3b82f6');

    expect($gradient->direction)->toBe(GradientDirection::Vertical);
});

it('stores from, to, and direction', function () {
    $gradient = new Gradient('red', 'blue', GradientDirection::Diagonal);

    expect($gradient->from)->toBe('red')
        ->and($gradient->to)->toBe('blue')
        ->and($gradient->direction)->toBe(GradientDirection::Diagonal);
});

it('throws when the from color is invalid', function () {
    new Gradient('not-a-color', '#3b82f6');
})->throws(InvalidArgumentException::class, 'Invalid gradient color: not-a-color');

it('throws when the to color is invalid', function () {
    new Gradient('#10b981', 'not-a-color');
})->throws(InvalidArgumentException::class, 'Invalid gradient color: not-a-color');
