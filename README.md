# Preview

A simple package to generate a preview image.

## Installation

Install the package with composer.

```bash
composer require yilanboy/preview
```

Then create an image builder.

```php
use Yilanboy\Preview\Image\Builder;
use Yilanboy\Preview\Image\TextBlock;
use Yilanboy\Preview\Image\Enums\FontSize;

new Builder
    ->size(width: 1200, height: 600)
    ->backgroundColor('#777bb3')
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

Available customization enums live under `Yilanboy\Preview\Image\Enums`:

| Enum | Cases |
|---|---|
| `Font` | `NotoSansTC` |
| `FontSize` | `ExtraSmall` (24) · `Small` (32) · `Medium` (50) · `Large` (64) · `ExtraLarge` (80) · `Huge` (100) |
| `Alignment` | `Left` · `Center` · `Right` |

> Currently, the text only supports English and Chinese.

## Start a Local Server to Show the Image

There is a `playground.php` file in `examples` folder. Start a local server to open an interactive form where you can edit the title, description, colors, font, and font size, then see the rendered image update below.

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
