<?php

use Yilanboy\Preview\Canvas\Enums\Format;
use Yilanboy\Preview\Canvas\Enums\GradientDirection;
use Yilanboy\Preview\Canvas\Enums\ImageFit;
use Yilanboy\Preview\Canvas\Enums\Size;
use Yilanboy\Preview\Canvas\Gradient;
use Yilanboy\Preview\Canvas\Image as ImageBackground;
use Yilanboy\Preview\Canvas\Solid;
use Yilanboy\Preview\Generator;
use Yilanboy\Preview\Text\Enums\Alignment;
use Yilanboy\Preview\Text\Enums\Font;
use Yilanboy\Preview\Text\Enums\LineHeight;
use Yilanboy\Preview\Text\Enums\Position;
use Yilanboy\Preview\Text\TextBlock;

it('can save png image', function () {
    $filename = 'test.png';

    new Generator()
        ->size(Size::OpenGraph)
        ->background(new Solid('#10b981'))
        ->description(new TextBlock(text: 'A true master is an eternal student', color: 'white'))
        ->save($filename);

    expect(file_exists($filename))->toBeTrue();
    unlink($filename);
});

it('matches snapshot', function () {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $fixture = __DIR__.'/../Fixtures/snapshot.png';

    new Generator()
        ->size(Size::OpenGraph)
        ->background(new Solid('#10b981'))
        ->title(new TextBlock(text: 'My Blog'))
        ->description(new TextBlock(text: 'A true master is an eternal student', color: 'white'))
        ->save($actual);

    if (getenv('UPDATE_SNAPSHOTS') || ! file_exists($fixture)) {
        copy($actual, $fixture);
    }

    expect(imagesMatch($actual, $fixture))->toBeTrue();

    unlink($actual);
});

it('stacks title above description without overlap when they share a position', function (Position $position) {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';

    // Title near-black (#030712), description white (#ffffff), background green.
    new Generator()
        ->size(Size::OpenGraph)
        ->background(new Solid('#10b981'))
        ->title(new TextBlock(text: 'My Blog', position: $position))
        ->description(new TextBlock(text: 'A true master is an eternal student', color: 'white', position: $position))
        ->save($actual);

    $image = imagecreatefrompng($actual);

    if ($image === false) {
        throw new RuntimeException('Failed to read the generated image');
    }

    $width = imagesx($image);
    $height = imagesy($image);

    // Classify each row by the text color it contains.
    $titleRows = [];
    $descriptionRows = [];
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $rgb = imagecolorat($image, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;

            if ($r < 40 && $g < 40 && $b < 40) {
                $titleRows[$y] = true;
            } elseif ($r > 220 && $g > 220 && $b > 220) {
                $descriptionRows[$y] = true;
            }
        }
    }

    if ($titleRows === [] || $descriptionRows === []) {
        throw new RuntimeException('Expected both title and description glyph rows to be present');
    }

    // No row contains both glyph colors, and the title band sits entirely
    // above the description band.
    expect(array_intersect_key($titleRows, $descriptionRows))->toBeEmpty()
        ->and(max(array_keys($titleRows)))->toBeLessThan(min(array_keys($descriptionRows)));

    unlink($actual);
})->with([Position::Top, Position::Center, Position::Bottom]);

it('fails snapshot when title color changes', function () {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $fixture = __DIR__.'/../Fixtures/snapshot.png';

    new Generator()
        ->size(Size::OpenGraph)
        ->background(new Solid('#10b981'))
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
        ->size(Size::OpenGraph)
        ->background(new Solid('#10b981'))
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
        ->size(Size::OpenGraph)
        ->background(new Solid('red'))
        ->title(new TextBlock(text: 'My Blog'))
        ->description(new TextBlock(text: 'A true master is an eternal student', color: 'white'))
        ->save($actual);

    expect(imagesMatch($actual, $fixture))->toBeFalse();

    unlink($actual);
});

it('renders with a gradient background', function (GradientDirection $direction) {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';

    new Generator()
        ->size(Size::OpenGraph)
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
        ->size(Size::OpenGraph)
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
        ->size(Size::OpenGraph)
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
        ->size(Size::OpenGraph)
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
        ->size(Size::OpenGraph)
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
        ->size(Size::Square)
        ->background(new Gradient('#ef4444', '#1d4ed8', GradientDirection::Diagonal))
        ->save($source);

    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $fixture = __DIR__.'/../Fixtures/image-cover.png';

    new Generator()
        ->size(Size::OpenGraph)
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
        ->size(Size::OpenGraph)
        ->background(new Solid('#10b981'))
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

it('matches Japanese text snapshot', function () {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $fixture = __DIR__.'/../Fixtures/japanese-text.png';

    new Generator()
        ->size(Size::OpenGraph)
        ->background(new Solid('#10b981'))
        ->title(new TextBlock(text: '私のブログ', font: Font::NotoSansJP))
        ->description(new TextBlock(text: '真のマスターは、永遠に学徒の心をもつ', color: 'white',
            font: Font::NotoSansJP))
        ->save($actual);

    if (getenv('UPDATE_SNAPSHOTS') || ! file_exists($fixture)) {
        copy($actual, $fixture);
    }

    expect(imagesMatch($actual, $fixture))->toBeTrue();

    unlink($actual);
});

it('matches Simplified Chinese text snapshot', function () {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $fixture = __DIR__.'/../Fixtures/simplified-chinese-text.png';

    new Generator()
        ->size(Size::OpenGraph)
        ->background(new Solid('#10b981'))
        ->title(new TextBlock(text: '我的部落格', font: Font::NotoSansTC))
        ->description(new TextBlock(text: '真正的大师，永远怀着一颗学徒的心', color: 'white', font: Font::NotoSansTC))
        ->save($actual);

    if (getenv('UPDATE_SNAPSHOTS') || ! file_exists($fixture)) {
        copy($actual, $fixture);
    }

    expect(imagesMatch($actual, $fixture))->toBeTrue();

    unlink($actual);
});

it('matches Traditional Chinese text snapshot', function () {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $fixture = __DIR__.'/../Fixtures/traditional-chinese-text.png';

    new Generator()
        ->size(Size::OpenGraph)
        ->background(new Solid('#10b981'))
        ->title(new TextBlock(text: '我的部落格', font: Font::NotoSansTC))
        ->description(new TextBlock(text: '真正的大師，永遠懷著一顆學徒的心', color: 'white', font: Font::NotoSansTC))
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
        ->size(Size::OpenGraph)
        ->background(new Solid('#10b981'))
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
        ->size(Size::OpenGraph)
        ->background(new Solid('#10b981'))
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

it('matches long-wrapping-text-loose snapshot', function () {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';
    $fixture = __DIR__.'/../Fixtures/long-wrapping-text-loose.png';

    new Generator()
        ->size(Size::OpenGraph)
        ->background(new Solid('#10b981'))
        ->title(new TextBlock(text: 'My Blog'))
        ->description(new TextBlock(
            text: 'The quick brown fox jumps over the lazy dog while the early bird catches the worm and a stitch in time saves nine',
            color: 'white',
            lineHeight: LineHeight::Loose,
        ))
        ->save($actual);

    if (getenv('UPDATE_SNAPSHOTS') || ! file_exists($fixture)) {
        copy($actual, $fixture);
    }

    expect(imagesMatch($actual, $fixture))->toBeTrue();

    unlink($actual);
});

it('renders with a custom font supplied as a file path', function () {
    $actual = tempnam(sys_get_temp_dir(), 'preview_').'.png';

    new Generator()
        ->size(Size::OpenGraph)
        ->background(new Solid('#10b981'))
        ->title(new TextBlock(text: 'Custom font', font: Font::Inter->path()))
        ->save($actual);

    expect(file_exists($actual))->toBeTrue()
        ->and(filesize($actual))->toBeGreaterThan(0);

    unlink($actual);
});

it('saves in the requested format', function (Format $format, string $ext) {
    $path = tempnam(sys_get_temp_dir(), 'preview_').".$ext";

    new Generator()
        ->format($format)
        ->background(new Solid('#10b981'))
        ->title(new TextBlock(text: 'My Blog'))
        ->save($path);

    expect(filesize($path))->toBeGreaterThan(0)
        ->and(mime_content_type($path))->toBe($format->mimeType());
})->with([
    [Format::PNG, 'png'],
    [Format::JPEG, 'jpg'],
    [Format::WEBP, 'webp'],
]);

it('uses the configured format, not the file extension', function () {
    $path = tempnam(sys_get_temp_dir(), 'preview_').'.png';

    new Generator()
        ->format(Format::JPEG)
        ->background(new Solid('#10b981'))
        ->title(new TextBlock(text: 'My Blog'))
        ->save($path);

    // The file is named .png, but format(JPEG) is the source of truth,
    // so the bytes on disk are JPEG.
    expect(mime_content_type($path))->toBe(Format::JPEG->mimeType());

    unlink($path);
});

it('can return the image bytes', function (Format $format) {
    $bytes = new Generator()
        ->format($format)
        ->dimensions(width: 100, height: 100)
        ->background(new Solid('#10b981'))
        ->title(new TextBlock(text: 'Return bytes'))
        ->bytes();

    expect($bytes)->toBeString()
        ->and($bytes)->not->toBeEmpty()
        ->and(new finfo(FILEINFO_MIME_TYPE)->buffer($bytes))->toBe($format->mimeType());
})->with([
    Format::PNG,
    Format::JPEG,
    Format::WEBP,
]);
