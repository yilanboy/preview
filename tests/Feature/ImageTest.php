<?php

use Yilanboy\Preview\Image\Builder;
use Yilanboy\Preview\Image\TextBlock;

it('can save png image', function () {
    $filename = 'test.png';

    (new Builder)
        ->size(width: 1200, height: 600)
        ->backgroundColor('#10b981')
        ->description(new TextBlock(text: 'A true master is an eternal student', color: 'white'))
        ->save($filename);

    expect(file_exists($filename))->toBeTrue();
    unlink($filename);
});

it('matches snapshot', function () {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $fixture = __DIR__.'/../Fixtures/snapshot.png';

    (new Builder)
        ->size(width: 1200, height: 600)
        ->backgroundColor('#10b981')
        ->title(new TextBlock(text: 'My Blog'))
        ->description(new TextBlock(text: 'A true master is an eternal student', color: 'white'))
        ->save($actual);

    if (getenv('UPDATE_SNAPSHOTS') || ! file_exists($fixture)) {
        copy($actual, $fixture);
    }

    expect(imagesMatch($actual, $fixture))->toBeTrue();

    unlink($actual);
});

it('fails snapshot when title color changes', function () {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $fixture = __DIR__.'/../Fixtures/snapshot.png';

    (new Builder)
        ->size(width: 1200, height: 600)
        ->backgroundColor('#10b981')
        ->title(new TextBlock(text: 'My Blog', color: 'red'))
        ->description(new TextBlock(text: 'A true master is an eternal student', color: 'white'))
        ->save($actual);

    expect(imagesMatch($actual, $fixture))->toBeFalse();

    unlink($actual);
});

it('fails snapshot when description color changes', function () {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $fixture = __DIR__.'/../Fixtures/snapshot.png';

    (new Builder)
        ->size(width: 1200, height: 600)
        ->backgroundColor('#10b981')
        ->title(new TextBlock(text: 'My Blog'))
        ->description(new TextBlock(text: 'A true master is an eternal student', color: 'red'))
        ->save($actual);

    expect(imagesMatch($actual, $fixture))->toBeFalse();

    unlink($actual);
});

it('fails snapshot when background color changes', function () {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $fixture = __DIR__.'/../Fixtures/snapshot.png';

    (new Builder)
        ->size(width: 1200, height: 600)
        ->backgroundColor('red')
        ->title(new TextBlock(text: 'My Blog'))
        ->description(new TextBlock(text: 'A true master is an eternal student', color: 'white'))
        ->save($actual);

    expect(imagesMatch($actual, $fixture))->toBeFalse();

    unlink($actual);
});
