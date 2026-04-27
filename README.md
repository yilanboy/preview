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

(new Builder())
    ->size(width: 1200, height: 600)
    ->backgroundColor('#777bb3')
    ->header(text: 'Preview', color: 'white', fontSize: 75)
    ->title(text: 'A simple PHP package to create preview image', color: 'white', fontSize: 50)
    ->output();
```

This code will display the following image on the web page.

![preview](images/preview.png)

You can modify the text on the preview image.

> Currently, the text only supports English and Chinese.

## Start a Local Server to Show the Image

There is a `output.php` file in `examples` folder, you can start a local server to see the image in browser.

```bash
php -S localhost:8000 examples/output.php
```

Then open your browser and visit [localhost:8000](http://localhost:8000).

## Run Tests

```bash
composer run-tests
```
