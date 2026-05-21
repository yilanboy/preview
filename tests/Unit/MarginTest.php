<?php

use Yilanboy\Preview\Canvas\Enums\Margin;

it('exposes pixel values for each preset', function () {
    expect(Margin::None->value)->toBe(0)
        ->and(Margin::Small->value)->toBe(30)
        ->and(Margin::Medium->value)->toBe(60)
        ->and(Margin::Large->value)->toBe(90)
        ->and(Margin::ExtraLarge->value)->toBe(120);
});

it('reports a non-negative pixel value for every preset', function (Margin $margin) {
    expect($margin->value)->toBeGreaterThanOrEqual(0);
})->with(Margin::cases());
