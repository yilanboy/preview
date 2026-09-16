<?php

use Yilanboy\Preview\Text\Enums\Font;
use Yilanboy\Preview\Text\FontStorage;

afterEach(function () {
    FontStorage::reset();
});

it('has a .ttf filename for every font case', function (Font $font) {
    expect($font->value)->toEndWith('.ttf');
})->with(Font::cases());

it('resolves font path from the configured storage directory when cached', function (Font $font) {
    $tempDir = sys_get_temp_dir().'/preview-test-font-path';
    if (! is_dir($tempDir)) {
        mkdir($tempDir, 0777, true);
    }
    $targetFile = $tempDir.'/'.$font->value;
    file_put_contents($targetFile, 'dummy-content');

    FontStorage::setStoragePath($tempDir);

    expect($font->path())->toBe($targetFile);

    unlink($targetFile);
})->with(Font::cases());
