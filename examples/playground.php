<?php

include_once __DIR__.'/../vendor/autoload.php';

use Yilanboy\Preview\Image\Builder;
use Yilanboy\Preview\Image\Enums\Font;
use Yilanboy\Preview\Image\Enums\FontSize;
use Yilanboy\Preview\Image\TextBlock;

const DEFAULT_TITLE_TEXT = 'My Blog';
const DEFAULT_TITLE_COLOR = '#ffffff';
const DEFAULT_DESC_TEXT = 'A simple PHP package to create preview image';
const DEFAULT_DESC_COLOR = '#ffffff';
const DEFAULT_BG_COLOR = '#777bb3';

$titleData = is_array($_POST['title'] ?? null) ? $_POST['title'] : [];
$descriptionData = is_array($_POST['description'] ?? null) ? $_POST['description'] : [];
$backgroundData = is_array($_POST['background'] ?? null) ? $_POST['background'] : [];

$titleText = (string) ($titleData['text'] ?? DEFAULT_TITLE_TEXT);
$titleColor = ((string) ($titleData['color'] ?? '')) ?: DEFAULT_TITLE_COLOR;
$titleFont = Font::tryFrom((string) ($titleData['font'] ?? '')) ?? Font::NotoSansTC;
$titleSize = FontSize::tryFrom((int) ($titleData['fontSize'] ?? 0)) ?? FontSize::Large;

$descriptionText = (string) ($descriptionData['text'] ?? DEFAULT_DESC_TEXT);
$descriptionColor = ((string) ($descriptionData['color'] ?? '')) ?: DEFAULT_DESC_COLOR;
$descriptionFont = Font::tryFrom((string) ($descriptionData['font'] ?? '')) ?? Font::NotoSansTC;
$descriptionSize = FontSize::tryFrom((int) ($descriptionData['fontSize'] ?? 0)) ?? FontSize::Medium;

$backgroundColor = ((string) ($backgroundData['color'] ?? '')) ?: DEFAULT_BG_COLOR;

$builder = new Builder()
    ->size(width: 1200, height: 600)
    ->backgroundColor($backgroundColor);

if ($titleText !== '') {
    $builder->title(new TextBlock(
        text: $titleText,
        color: $titleColor,
        fontSize: $titleSize,
        font: $titleFont,
    ));
}

if ($descriptionText !== '') {
    $builder->description(new TextBlock(
        text: $descriptionText,
        color: $descriptionColor,
        fontSize: $descriptionSize,
        font: $descriptionFont,
    ));
}

$tmpFile = tempnam(sys_get_temp_dir(), 'preview_').'.png';
$builder->save($tmpFile);
$imageBytes = file_get_contents($tmpFile);
unlink($tmpFile);
$imageDataUri = 'data:image/png;base64,'.base64_encode($imageBytes !== false ? $imageBytes : '');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Preview Playground</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 800px; margin: 2rem auto; padding: 0 1rem; color: #1f2937; }
        h1 { font-size: 1.5rem; }
        h2 { font-size: 1.125rem; margin-top: 2rem; color: #4b5563; }
        form { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1rem; }
        fieldset { border: 1px solid #e5e7eb; border-radius: 6px; padding: 1rem 1.25rem 1.25rem; display: flex; flex-direction: column; gap: 0.75rem; }
        legend { font-weight: 600; padding: 0 0.5rem; color: #1f2937; }
        label { display: flex; flex-direction: column; gap: 0.25rem; font-size: 0.875rem; color: #4b5563; }
        input, textarea, select { font: inherit; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; background: #fff; }
        input:focus, textarea:focus, select:focus { outline: 2px solid #777bb3; outline-offset: -1px; border-color: transparent; }
        textarea { resize: vertical; min-height: 4rem; }
        .row { display: flex; gap: 1rem; flex-wrap: wrap; }
        .row > label { flex: 1; min-width: 7rem; }
        .color-input { display: flex; align-items: stretch; border: 1px solid #d1d5db; border-radius: 6px; overflow: hidden; }
        .color-input:focus-within { outline: 2px solid #777bb3; outline-offset: -1px; border-color: transparent; }
        .color-swatch { width: 2rem; flex-shrink: 0; background: #ccc; border-right: 1px solid #d1d5db; }
        .color-input input { border: 0; border-radius: 0; flex: 1; min-width: 0; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
        .color-input input:focus { outline: 0; }
        button { padding: 0.625rem 1.25rem; font: inherit; background: #777bb3; color: #fff; border: 0; border-radius: 6px; cursor: pointer; width: fit-content; }
        button:hover { background: #65689c; }
        img { max-width: 100%; border: 1px solid #e5e7eb; border-radius: 6px; display: block; }
    </style>
</head>
<body>
    <h1>Preview Playground</h1>
    <form method="post">
        <fieldset>
            <legend>Background</legend>
            <label>
                Color
                <span class="color-input">
                    <span class="color-swatch" style="background: <?= htmlspecialchars($backgroundColor) ?>"></span>
                    <input name="background[color]" value="<?= htmlspecialchars($backgroundColor) ?>" autocomplete="off">
                </span>
            </label>
        </fieldset>

        <fieldset>
            <legend>Title</legend>
            <label>
                Text
                <input name="title[text]" value="<?= htmlspecialchars($titleText) ?>">
            </label>
            <div class="row">
                <label>
                    Color
                    <span class="color-input">
                        <span class="color-swatch" style="background: <?= htmlspecialchars($titleColor) ?>"></span>
                        <input name="title[color]" value="<?= htmlspecialchars($titleColor) ?>" autocomplete="off">
                    </span>
                </label>
                <label>
                    Font
                    <select name="title[font]">
                        <?php foreach (Font::cases() as $font) { ?>
                            <option value="<?= htmlspecialchars($font->value) ?>" <?= $font === $titleFont ? 'selected' : '' ?>>
                                <?= $font->name ?>
                            </option>
                        <?php } ?>
                    </select>
                </label>
                <label>
                    Size
                    <select name="title[fontSize]">
                        <?php foreach (FontSize::cases() as $size) { ?>
                            <option value="<?= $size->value ?>" <?= $size === $titleSize ? 'selected' : '' ?>>
                                <?= $size->name ?> (<?= $size->value ?>px)
                            </option>
                        <?php } ?>
                    </select>
                </label>
            </div>
        </fieldset>

        <fieldset>
            <legend>Description</legend>
            <label>
                Text
                <textarea name="description[text]" rows="3"><?= htmlspecialchars($descriptionText) ?></textarea>
            </label>
            <div class="row">
                <label>
                    Color
                    <span class="color-input">
                        <span class="color-swatch" style="background: <?= htmlspecialchars($descriptionColor) ?>"></span>
                        <input name="description[color]" value="<?= htmlspecialchars($descriptionColor) ?>" autocomplete="off">
                    </span>
                </label>
                <label>
                    Font
                    <select name="description[font]">
                        <?php foreach (Font::cases() as $font) { ?>
                            <option value="<?= htmlspecialchars($font->value) ?>" <?= $font === $descriptionFont ? 'selected' : '' ?>>
                                <?= $font->name ?>
                            </option>
                        <?php } ?>
                    </select>
                </label>
                <label>
                    Size
                    <select name="description[fontSize]">
                        <?php foreach (FontSize::cases() as $size) { ?>
                            <option value="<?= $size->value ?>" <?= $size === $descriptionSize ? 'selected' : '' ?>>
                                <?= $size->name ?> (<?= $size->value ?>px)
                            </option>
                        <?php } ?>
                    </select>
                </label>
            </div>
        </fieldset>

        <button type="submit">Generate Preview</button>
    </form>

    <h2>Result</h2>
    <img src="<?= $imageDataUri ?>" alt="Generated preview image">

    <script>
        document.querySelectorAll('.color-input').forEach(function (wrapper) {
            const input = wrapper.querySelector('input');
            const swatch = wrapper.querySelector('.color-swatch');
            input.addEventListener('input', function () {
                if (/^#[0-9a-fA-F]{6}$/.test(input.value)) {
                    swatch.style.background = input.value;
                }
            });
        });
    </script>
</body>
</html>
