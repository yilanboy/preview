<?php

use Yilanboy\Preview\Image\Background\Gradient;
use Yilanboy\Preview\Image\Enums\GradientDirection;

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
