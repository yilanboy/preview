# Preview

A simple package to generate a preview image.

## Installation

Install the package with composer.

```bash
composer require yilanboy/preview
```

Then create an image generator.

```php
use Yilanboy\Preview\Canvas\Background\Solid;
use Yilanboy\Preview\Canvas\Enums\Size;
use Yilanboy\Preview\Generator;
use Yilanboy\Preview\Text\Enums\FontSize;
use Yilanboy\Preview\Text\TextBlock;

new Generator
    ->size(Size::OpenGraph)
    ->background(new Solid('#777bb3'))
    ->title(new TextBlock(
        text: 'Preview',
        color: 'white',
        fontSize: FontSize::Large,
    ))
    ->description(new TextBlock(
        text: 'A simple PHP package to create preview image',
        color: 'white',
        fontSize: FontSize::Medium,
    ))
    ->output();
```

This code will display the following image on the web page.

![preview](images/preview.png)

`TextBlock` is immutable. Use the `with*()` methods to derive a modified copy:

```php
$base = new TextBlock(text: 'Hello');
$red  = $base->withColor('red');
$big  = $base->withFontSize(FontSize::Huge);
```

Available customization enums live under `Yilanboy\Preview\Canvas\Enums`:

| Enum        | Cases                                                                                              |
|-------------|----------------------------------------------------------------------------------------------------|
| `Font`      | `NotoSansTC` · `NotoSans` · `Inter` · `Roboto`                                                     |
| `FontSize`  | `ExtraSmall` (24) · `Small` (32) · `Medium` (50) · `Large` (64) · `ExtraLarge` (80) · `Huge` (100) |
| `Alignment` | `Left` · `Center` · `Right`                                                                        |

All four bundled fonts are variable-weight TTFs shipped under SIL OFL. `NotoSansTC` covers Latin + Traditional Chinese;
the other three are Latin-only.

> Currently, the text only supports English and Chinese.

## Canvas Size

Pick a preset that matches where the image will be embedded. `Generator` defaults to `Size::OpenGraph`.

```php
use Yilanboy\Preview\Canvas\Enums\Size;

$generator->size(Size::Square);
```

| Preset      | Dimensions  | Where it's used                       |
|-------------|-------------|---------------------------------------|
| `OpenGraph` | 1200 × 630  | Facebook, generic Open Graph previews |
| `Square`    | 1080 × 1080 | Instagram, LinkedIn square posts      |

## Backgrounds

`Generator::background()` accepts anything implementing the `Background` interface. Three implementations ship with the
package.

**Solid** — a flat color.

```php
use Yilanboy\Preview\Canvas\Background\Solid;

$generator->background(new Solid('#777bb3'));
```

**Gradient** — two colors interpolated across the canvas.

```php
use Yilanboy\Preview\Canvas\Background\Gradient;
use Yilanboy\Preview\Canvas\Enums\GradientDirection;

$generator->background(new Gradient(
    from: '#1e3a8a',
    to: '#9333ea',
    direction: GradientDirection::Diagonal,
));
```

`GradientDirection` cases: `Vertical` (default) · `Horizontal` · `Diagonal`.

**Image** — render a bitmap behind your text.

```php
use Yilanboy\Preview\Canvas\Background\Image;
use Yilanboy\Preview\Canvas\Enums\ImageFit;

$generator->background(new Image(
    path: __DIR__.'/cover.jpg',
    fit: ImageFit::Cover,
    opacity: 0.6,
    tint: '#000000',
));
```

`ImageFit` cases: `Cover` (default) · `Contain` · `Stretch` · `Tile`.

`opacity` is a float between `0.0` and `1.0` (default `1.0`). When `opacity < 1.0`, the canvas is filled with `tint`
first so the tint color shows through the partially transparent image — use it to darken or wash the background. `tint`
defaults to `#000000`.

See all three modes interactively in the playground (next section).

## Start a Local Server to Show the Image

There is a `playground.php` file in `examples` folder. Start a local server to open an interactive form where you can
edit the title, description, font, and font size, switch between **Solid / Gradient / Image** backgrounds, preview
gradients live via CSS, and tweak image opacity and tint with sliders.

```bash
php -S localhost:8000 examples/playground.php
```

Then open your browser and visit [localhost:8000](http://localhost:8000).

## Development

Run tests:

```bash
composer tests
```

Format code with Pint (only dirty files):

```bash
composer fmt
```

Run all checks (Pint + PHPStan):

```bash
composer check
```
