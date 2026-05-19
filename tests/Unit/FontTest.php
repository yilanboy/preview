<?php

use Yilanboy\Preview\Image\Enums\Font;

it('resolves path for NotoSansTC to an existing file', function () {
    expect(file_exists(Font::NotoSansTC->path()))->toBeTrue();
});
