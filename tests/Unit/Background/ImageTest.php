<?php

use Yilanboy\Preview\Image\Background\Image;
use Yilanboy\Preview\Image\Enums\ImageFit;

it('throws when the file does not exist', function () {
    new Image('/this/path/does/not/exist.png');
})->throws(InvalidArgumentException::class, 'Background image not found');

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
})->throws(InvalidArgumentException::class, 'Opacity must be between 0.0 and 1.0');

it('throws when opacity is above 1', function () {
    $fixture = __DIR__.'/../../Fixtures/snapshot.png';
    new Image($fixture, opacity: 1.5);
})->throws(InvalidArgumentException::class, 'Opacity must be between 0.0 and 1.0');
