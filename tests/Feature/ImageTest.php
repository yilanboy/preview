<?php

use Yilanboy\Preview\Canvas\Background\Gradient;
use Yilanboy\Preview\Canvas\Background\Image as ImageBackground;
use Yilanboy\Preview\Canvas\Enums\GradientDirection;
use Yilanboy\Preview\Canvas\Enums\ImageFit;
use Yilanboy\Preview\Generator;
use Yilanboy\Preview\Text\Enums\Alignment;
use Yilanboy\Preview\Text\Enums\Font;
use Yilanboy\Preview\Text\TextBlock;

it('can save png image', function () {
    $filename = 'test.png';

    new Generator()
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

    new Generator()
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

    new Generator()
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

    new Generator()
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

    new Generator()
        ->size(width: 1200, height: 600)
        ->backgroundColor('red')
        ->title(new TextBlock(text: 'My Blog'))
        ->description(new TextBlock(text: 'A true master is an eternal student', color: 'white'))
        ->save($actual);

    expect(imagesMatch($actual, $fixture))->toBeFalse();

    unlink($actual);
});

it('renders with a gradient background', function (GradientDirection $direction) {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';

    new Generator()
        ->size(width: 1200, height: 600)
        ->background(new Gradient('#10b981', '#3b82f6', $direction))
        ->title(new TextBlock(text: 'Gradient', color: 'white'))
        ->save($actual);

    expect(file_exists($actual))->toBeTrue()
        ->and(filesize($actual))->toBeGreaterThan(0);

    unlink($actual);
})->with(GradientDirection::cases());

it('renders with an image background', function (ImageFit $fit) {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $bg = __DIR__.'/../Fixtures/snapshot.png';

    new Generator()
        ->size(width: 1200, height: 600)
        ->background(new ImageBackground($bg, $fit))
        ->title(new TextBlock(text: 'Image bg', color: 'white'))
        ->save($actual);

    expect(file_exists($actual))->toBeTrue()
        ->and(filesize($actual))->toBeGreaterThan(0);

    unlink($actual);
})->with(ImageFit::cases());

it('renders with a semi-transparent image background', function () {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $bg = __DIR__.'/../Fixtures/snapshot.png';

    new Generator()
        ->size(width: 1200, height: 600)
        ->background(new ImageBackground($bg, ImageFit::Cover, opacity: 0.3, tint: '#000000'))
        ->title(new TextBlock(text: 'Dimmed', color: 'white'))
        ->save($actual);

    expect(file_exists($actual))->toBeTrue()
        ->and(filesize($actual))->toBeGreaterThan(0);

    unlink($actual);
});

it('matches gradient-vertical snapshot', function () {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $fixture = __DIR__.'/../Fixtures/gradient-vertical.png';

    new Generator()
        ->size(width: 1200, height: 600)
        ->background(new Gradient('#10b981', '#3b82f6', GradientDirection::Vertical))
        ->title(new TextBlock(text: 'My Blog', color: 'white'))
        ->description(new TextBlock(text: 'A true master is an eternal student', color: 'white'))
        ->save($actual);

    if (getenv('UPDATE_SNAPSHOTS') || ! file_exists($fixture)) {
        copy($actual, $fixture);
    }

    expect(imagesMatch($actual, $fixture))->toBeTrue();

    unlink($actual);
});

it('matches gradient-diagonal snapshot', function () {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $fixture = __DIR__.'/../Fixtures/gradient-diagonal.png';

    new Generator()
        ->size(width: 1200, height: 600)
        ->background(new Gradient('#fb923c', '#7c3aed', GradientDirection::Diagonal))
        ->title(new TextBlock(text: 'My Blog', color: 'white'))
        ->description(new TextBlock(text: 'A true master is an eternal student', color: 'white'))
        ->save($actual);

    if (getenv('UPDATE_SNAPSHOTS') || ! file_exists($fixture)) {
        copy($actual, $fixture);
    }

    expect(imagesMatch($actual, $fixture))->toBeTrue();

    unlink($actual);
});

it('matches image-cover snapshot', function () {
    $source = tempnam(sys_get_temp_dir(), 'src_').'.png';
    new Generator()
        ->size(width: 400, height: 200)
        ->background(new Gradient('#ef4444', '#1d4ed8', GradientDirection::Diagonal))
        ->save($source);

    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $fixture = __DIR__.'/../Fixtures/image-cover.png';

    new Generator()
        ->size(width: 1200, height: 600)
        ->background(new ImageBackground($source, ImageFit::Cover))
        ->title(new TextBlock(text: 'Image bg', color: 'white'))
        ->save($actual);

    unlink($source);

    if (getenv('UPDATE_SNAPSHOTS') || ! file_exists($fixture)) {
        copy($actual, $fixture);
    }

    expect(imagesMatch($actual, $fixture))->toBeTrue();

    unlink($actual);
});

it('matches inter-font snapshot', function () {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $fixture = __DIR__.'/../Fixtures/inter-font.png';

    new Generator()
        ->size(width: 1200, height: 600)
        ->backgroundColor('#10b981')
        ->title(new TextBlock(text: 'My Blog', font: Font::Inter))
        ->description(new TextBlock(text: 'A true master is an eternal student', color: 'white', font: Font::Inter))
        ->save($actual);

    if (getenv('UPDATE_SNAPSHOTS') || ! file_exists($fixture)) {
        copy($actual, $fixture);
    }

    // Variable fonts (Inter) have higher cross-platform FreeType variance than
    // the static-weight Noto Sans TC — relax the cluster threshold accordingly.
    expect(imagesMatch($actual, $fixture, clusterThreshold: 0.01))->toBeTrue();

    unlink($actual);
});

it('matches chinese-text snapshot', function () {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $fixture = __DIR__.'/../Fixtures/chinese-text.png';

    new Generator()
        ->size(width: 1200, height: 600)
        ->backgroundColor('#10b981')
        ->title(new TextBlock(text: '我的部落格'))
        ->description(new TextBlock(text: '真正的大師，永遠懷著一顆學徒的心', color: 'white'))
        ->save($actual);

    if (getenv('UPDATE_SNAPSHOTS') || ! file_exists($fixture)) {
        copy($actual, $fixture);
    }

    expect(imagesMatch($actual, $fixture))->toBeTrue();

    unlink($actual);
});

it('matches centered-text snapshot', function () {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $fixture = __DIR__.'/../Fixtures/centered-text.png';

    new Generator()
        ->size(width: 1200, height: 600)
        ->backgroundColor('#10b981')
        ->title(new TextBlock(text: 'My Blog', alignment: Alignment::Center))
        ->description(new TextBlock(
            text: 'A true master is an eternal student',
            color: 'white',
            alignment: Alignment::Center,
        ))
        ->save($actual);

    if (getenv('UPDATE_SNAPSHOTS') || ! file_exists($fixture)) {
        copy($actual, $fixture);
    }

    expect(imagesMatch($actual, $fixture))->toBeTrue();

    unlink($actual);
});

it('matches long-wrapping-text snapshot', function () {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $fixture = __DIR__.'/../Fixtures/long-wrapping-text.png';

    new Generator()
        ->size(width: 1200, height: 600)
        ->backgroundColor('#10b981')
        ->title(new TextBlock(text: 'My Blog'))
        ->description(new TextBlock(
            text: 'The quick brown fox jumps over the lazy dog while the early bird catches the worm and a stitch in time saves nine',
            color: 'white',
        ))
        ->save($actual);

    if (getenv('UPDATE_SNAPSHOTS') || ! file_exists($fixture)) {
        copy($actual, $fixture);
    }

    expect(imagesMatch($actual, $fixture))->toBeTrue();

    unlink($actual);
});
