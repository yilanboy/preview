<?php

use Yilanboy\Preview\Image\Builder;

it('can save png image', function () {
    $filename = 'test.png';

    (new Builder())
        ->size(width: 1200, height: 600)
        ->backgroundColor('#10b981')
        ->title(text: 'A true master is an eternal student', color: 'white')
        ->save($filename);

    expect(file_exists($filename))->toBeTrue();
    unlink($filename);
});

it('matches snapshot', function () {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $fixture = __DIR__.'/../Fixtures/snapshot.png';

    (new Builder())
        ->size(width: 1200, height: 600)
        ->backgroundColor('#10b981')
        ->header(text: 'My Blog')
        ->title(text: 'A true master is an eternal student', color: 'white')
        ->save($actual);

    if (getenv('UPDATE_SNAPSHOTS') || ! file_exists($fixture)) {
        copy($actual, $fixture);
    }

    expect(imagesMatch($actual, $fixture))->toBeTrue();

    unlink($actual);
});
