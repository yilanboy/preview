<?php

use Yilanboy\Preview\Text\Enums\LineHeight;

it('exposes multiplier values for each preset', function () {
    expect(LineHeight::Snug->multiplier())->toBe(1.15)
        ->and(LineHeight::Normal->multiplier())->toBe(1.3)
        ->and(LineHeight::Relaxed->multiplier())->toBe(1.5)
        ->and(LineHeight::Loose->multiplier())->toBe(1.75);
});

it('reports a positive multiplier for every preset', function (LineHeight $lineHeight) {
    expect($lineHeight->multiplier())->toBeGreaterThan(0);
})->with(LineHeight::cases());
