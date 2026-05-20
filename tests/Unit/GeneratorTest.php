<?php

use Yilanboy\Preview\Canvas\Enums\Size;
use Yilanboy\Preview\Generator;

it('applies a size preset', function () {
    $generator = new Generator()->size(Size::Square);

    $reflection = new ReflectionClass($generator);
    $width = $reflection->getProperty('width')->getValue($generator);
    $height = $reflection->getProperty('height')->getValue($generator);

    expect($width)->toBe(1080)
        ->and($height)->toBe(1080);
});
