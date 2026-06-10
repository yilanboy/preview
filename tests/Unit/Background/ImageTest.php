<?php

use Yilanboy\Preview\Canvas\Background\Image;
use Yilanboy\Preview\Canvas\Enums\ImageFit;
use Yilanboy\Preview\Exceptions\InvalidInput;

it('throws when the file does not exist', function () {
    new Image('/this/path/does/not/exist.png');
})->throws(InvalidInput::class, 'Background image not found');

it('throws when the file exists but is not a valid image', function () {
    $fake = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    file_put_contents($fake, 'this is not a png');

    try {
        new Image($fake);
    } finally {
        unlink($fake);
    }
})->throws(InvalidInput::class, 'Invalid background image');

it('defaults to Cover fit', function () {
    $fixture = __DIR__.'/../../Fixtures/snapshot.png';
    $bg = new Image($fixture);

    expect($bg->fit)->toBe(ImageFit::Cover);
});

it('stores path and fit', function () {
    $fixture = __DIR__.'/../../Fixtures/snapshot.png';
    $bg = new Image($fixture, ImageFit::Tile);

    expect($bg->path)->toBe($fixture)
        ->and($bg->fit)->toBe(ImageFit::Tile);
});

it('defaults opacity to 1.0 and tint to #000000', function () {
    $fixture = __DIR__.'/../../Fixtures/snapshot.png';
    $bg = new Image($fixture);

    expect($bg->opacity)->toBe(1.0)
        ->and($bg->tint)->toBe('#000000');
});

it('throws when opacity is below 0', function () {
    $fixture = __DIR__.'/../../Fixtures/snapshot.png';
    new Image($fixture, opacity: -0.1);
})->throws(InvalidInput::class, 'Opacity must be between 0.0 and 1.0');

it('throws when opacity is above 1', function () {
    $fixture = __DIR__.'/../../Fixtures/snapshot.png';
    new Image($fixture, opacity: 1.5);
})->throws(InvalidInput::class, 'Opacity must be between 0.0 and 1.0');

it('throws when the tint color is invalid', function () {
    $fixture = __DIR__.'/../../Fixtures/snapshot.png';
    new Image($fixture, tint: 'not-a-color');
})->throws(InvalidInput::class, 'Invalid color: not-a-color');
