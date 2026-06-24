<?php

use Yilanboy\Preview\Text\Enums\Font;
use Yilanboy\Preview\Text\FontValidator;

it('resolves every bundled font path to an existing file', function (Font $font) {
    expect(file_exists($font->path()))->toBeTrue();
})->with(Font::cases());

it('is a valid ttf file', function (Font $font) {
    expect(FontValidator::isValidTtf($font->path()))->toBeTrue();
})->with(Font::cases());
