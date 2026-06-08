<?php

use Yilanboy\Preview\Canvas\Enums\Size;

it('returns the Open Graph dimensions', function () {
    expect(Size::OpenGraph->width())->toBe(1200)
        ->and(Size::OpenGraph->height())->toBe(630);
});

it('returns the Square dimensions', function () {
    expect(Size::Square->width())->toBe(1080)
        ->and(Size::Square->height())->toBe(1080);
});

it('returns the Landscape dimensions', function () {
    expect(Size::Landscape->width())->toBe(1920)
        ->and(Size::Landscape->height())->toBe(1080);
});

it('returns the Portrait dimensions', function () {
    expect(Size::Portrait->width())->toBe(1080)
        ->and(Size::Portrait->height())->toBe(1920);
});

it('returns the YouTube dimensions', function () {
    expect(Size::YouTube->width())->toBe(1280)
        ->and(Size::YouTube->height())->toBe(720);
});

it('reports a positive width and height for every preset', function (Size $size) {
    expect($size->width())->toBeGreaterThan(0)
        ->and($size->height())->toBeGreaterThan(0);
})->with(Size::cases());
