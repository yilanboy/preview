<?php

use Yilanboy\Preview\Text\Enums\LineHeight;

it('exposes multiplier values for each preset', function () {
    expect(LineHeight::Tight->multiplier())->toBe(1.0)
        ->and(LineHeight::Snug->multiplier())->toBe(1.1)
        ->and(LineHeight::Normal->multiplier())->toBe(1.25)
        ->and(LineHeight::Relaxed->multiplier())->toBe(1.45)
        ->and(LineHeight::Loose->multiplier())->toBe(1.7);
});

it('reports a positive multiplier for every preset', function (LineHeight $lineHeight) {
    expect($lineHeight->multiplier())->toBeGreaterThan(0);
})->with(LineHeight::cases());
