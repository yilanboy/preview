<?php

use Yilanboy\Preview\Image\Enums\Font;

it('resolves every bundled font path to an existing file', function (Font $font) {
    expect(file_exists($font->path()))->toBeTrue();
})->with(Font::cases());
