<?php

use Yilanboy\Preview\Text\Enums\Font;
use Yilanboy\Preview\Text\FontValidator;

it('accepts a real TrueType font file', function () {
    expect(FontValidator::isValidTtf(Font::NotoSansTC->path()))->toBeTrue();
});

it('rejects a path that does not exist', function () {
    expect(FontValidator::isValidTtf('/no/such/font.ttf'))->toBeFalse();
});

it('rejects a non-ttf extension', function () {
    expect(FontValidator::isValidTtf(__FILE__))->toBeFalse();
});

it('rejects a file with a faked .ttf extension', function () {
    $fake = tempnam(sys_get_temp_dir(), 'fake').'.ttf';
    file_put_contents($fake, 'this is not a font');

    expect(FontValidator::isValidTtf($fake))->toBeFalse();

    unlink($fake);
});

it('rejects an OpenType (OTTO) font masquerading as .ttf', function () {
    $otf = tempnam(sys_get_temp_dir(), 'otf').'.ttf';
    // 'OTTO' sfnt tag followed by padding.
    file_put_contents($otf, "OTTO\x00\x00\x00\x00");

    expect(FontValidator::isValidTtf($otf))->toBeFalse();

    unlink($otf);
});

it('rejects a file shorter than the header', function () {
    $short = tempnam(sys_get_temp_dir(), 'short').'.ttf';
    file_put_contents($short, "\x00\x01"); // only 2 of the 4 header bytes

    expect(FontValidator::isValidTtf($short))->toBeFalse();

    unlink($short);
});
