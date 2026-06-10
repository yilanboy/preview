<?php

use Yilanboy\Preview\Canvas\Enums\Margin;
use Yilanboy\Preview\Canvas\Enums\Size;
use Yilanboy\Preview\Exceptions\InvalidInput;
use Yilanboy\Preview\Generator;

it('applies a size preset', function () {
    $generator = new Generator()->size(Size::Square);

    $reflection = new ReflectionClass($generator);
    $width = $reflection->getProperty('width')->getValue($generator);
    $height = $reflection->getProperty('height')->getValue($generator);

    expect($width)->toBe(1080)
        ->and($height)->toBe(1080);
});

it('applies custom dimensions', function () {
    $generator = new Generator()->dimensions(width: 800, height: 400);

    $reflection = new ReflectionClass($generator);
    $width = $reflection->getProperty('width')->getValue($generator);
    $height = $reflection->getProperty('height')->getValue($generator);

    expect($width)->toBe(800)
        ->and($height)->toBe(400);
});

it('rejects dimensions below 1', function (int $width, int $height) {
    new Generator()->dimensions($width, $height);
})->with([
    'zero width' => [0, 400],
    'negative height' => [800, -1],
])->throws(InvalidInput::class, 'Width and height must be at least 1');

it('defaults to a medium margin', function () {
    $generator = new Generator;

    $margin = new ReflectionClass($generator)
        ->getProperty('margin')
        ->getValue($generator);

    expect($margin)->toBe(Margin::Medium);
});

it('applies a margin preset', function () {
    $generator = new Generator()->margin(Margin::Large);

    $margin = new ReflectionClass($generator)
        ->getProperty('margin')
        ->getValue($generator);

    expect($margin)->toBe(Margin::Large);
});
