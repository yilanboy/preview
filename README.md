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
use Yilanboy\Preview\Canvas\Enums\Margin;
use Yilanboy\Preview\Canvas\Enums\Size;
use Yilanboy\Preview\Generator;
use Yilanboy\Preview\Text\Enums\Font;
use Yilanboy\Preview\Text\Enums\FontSize;
use Yilanboy\Preview\Text\Enums\LineHeight;
use Yilanboy\Preview\Text\TextBlock;

new Generator
    ->size(Size::OpenGraph)
    ->margin(Margin::Medium)
    ->background(new Solid('#777bb3'))
    ->title(new TextBlock(
        text: 'Preview',
        color: 'white',
        fontSize: FontSize::Large,
        font: Font::Inter,
    ))
    ->description(new TextBlock(
        text: 'A simple PHP package to create preview image',
        color: 'white',
        fontSize: FontSize::Small,
        font: Font::Inter,
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

Available customization enums live under `Yilanboy\Preview\Text\Enums`:

| Enum         | Cases                                                                                              |
|--------------|----------------------------------------------------------------------------------------------------|
| `Font`       | `NotoSansTC` · `NotoSansSC` · `NotoSansJP` · `NotoSans` · `Inter` · `Roboto`                       |
| `FontSize`   | `ExtraSmall` (24) · `Small` (32) · `Medium` (50) · `Large` (64) · `ExtraLarge` (80) · `Huge` (100) |
| `Alignment`  | `Left` · `Center` · `Right`                                                                        |
| `LineHeight` | `Snug` (1.15) · `Normal` (1.3) · `Relaxed` (1.5) · `Loose` (1.75)                                  |

All six bundled fonts are variable-weight TTFs shipped under SIL OFL. `NotoSansTC` covers Latin + Traditional Chinese,
`NotoSansSC` covers Latin + Simplified Chinese, and `NotoSansJP` covers Latin + Japanese; `NotoSans`, `Inter`, and
`Roboto` are Latin-only.

> Currently, the text supports English, Chinese (Traditional and Simplified), and Japanese.

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

## Margin

Text is inset from the canvas edges by a fixed pixel margin. `Generator` defaults to `Margin::Medium` (60px).

```php
use Yilanboy\Preview\Canvas\Enums\Margin;

$generator->margin(Margin::Large);
```

| Preset       | Pixels |
|--------------|--------|
| `None`       | 0      |
| `Small`      | 30     |
| `Medium`     | 60     |
| `Large`      | 90     |
| `ExtraLarge` | 120    |

## Line Height

When text wraps to multiple lines, `LineHeight` controls the spacing between them. The value is a unit-less multiplier
of the text's font size (CSS `line-height` semantics). `TextBlock` defaults to `LineHeight::Normal` (1.3×).

```php
use Yilanboy\Preview\Text\Enums\LineHeight;
use Yilanboy\Preview\Text\TextBlock;

$generator->description(new TextBlock(
    text: 'A longer description that wraps to multiple lines for demonstration purposes.',
    lineHeight: LineHeight::Loose,
));
```

| Preset    | Multiplier |
|-----------|------------|
| `Snug`    | 1.15×      |
| `Normal`  | 1.3×       |
| `Relaxed` | 1.5×       |
| `Loose`   | 1.75×      |

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
