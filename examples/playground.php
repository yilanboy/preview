<?php

include_once __DIR__.'/../vendor/autoload.php';

use Yilanboy\Preview\Canvas\Background\Gradient;
use Yilanboy\Preview\Canvas\Background\Image as ImageBackground;
use Yilanboy\Preview\Canvas\Background\Solid;
use Yilanboy\Preview\Canvas\Enums\GradientDirection;
use Yilanboy\Preview\Canvas\Enums\ImageFit;
use Yilanboy\Preview\Canvas\Enums\Margin;
use Yilanboy\Preview\Canvas\Enums\Size;
use Yilanboy\Preview\Generator;
use Yilanboy\Preview\Text\Enums\Alignment;
use Yilanboy\Preview\Text\Enums\Font;
use Yilanboy\Preview\Text\Enums\FontSize;
use Yilanboy\Preview\Text\Enums\LineHeight;
use Yilanboy\Preview\Text\Enums\Position;
use Yilanboy\Preview\Text\TextBlock;

const DEFAULT_TITLE_TEXT = 'My Blog';
const DEFAULT_TITLE_COLOR = '#ffffff';
const DEFAULT_DESC_TEXT = 'A simple PHP package to create preview image';
const DEFAULT_DESC_COLOR = '#ffffff';
const DEFAULT_BG_COLOR = '#777bb3';
const DEFAULT_GRADIENT_FROM = '#10b981';
const DEFAULT_GRADIENT_TO = '#3b82f6';

/**
 * Resolve an Alignment by case name. `Alignment` is not a backed enum, so we
 * can't use `tryFrom`; mirror the same lookup pattern the playground already
 * uses for ImageFit / GradientDirection.
 */
function resolveAlignment(mixed $value, Alignment $default = Alignment::Left): Alignment
{
    return match ((string) $value) {
        'Left' => Alignment::Left,
        'Center' => Alignment::Center,
        'Right' => Alignment::Right,
        default => $default,
    };
}

/**
 * Resolve a Position by case name. Mirrors resolveAlignment — Position is
 * not a backed enum either.
 */
function resolvePosition(mixed $value, Position $default): Position
{
    return match ((string) $value) {
        'Top' => Position::Top,
        'Center' => Position::Center,
        'Bottom' => Position::Bottom,
        default => $default,
    };
}

/**
 * Resolve a LineHeight by case name. LineHeight is not a backed enum since
 * its multipliers are floats; look it up by name like Position/Alignment.
 */
function resolveLineHeight(mixed $value, LineHeight $default = LineHeight::Normal): LineHeight
{
    return array_find(
        LineHeight::cases(),
        fn (LineHeight $case) => $case->name === (string) $value,
    ) ?? $default;
}

$canvasData = is_array($_POST['canvas'] ?? null) ? $_POST['canvas'] : [];
$titleData = is_array($_POST['title'] ?? null) ? $_POST['title'] : [];
$descriptionData = is_array($_POST['description'] ?? null) ? $_POST['description'] : [];
$backgroundData = is_array($_POST['background'] ?? null) ? $_POST['background'] : [];

// Resolve the selected Size preset. Size is not a backed enum, so mirror the
// case-name lookup used elsewhere in the playground.
$sizeName = (string) ($canvasData['size'] ?? '');
$canvasSize = array_find(
    Size::cases(),
    fn (Size $case) => $case->name === $sizeName,
) ?? Size::OpenGraph;

$canvasMargin = Margin::tryFrom((int) ($canvasData['margin'] ?? 0)) ?? Margin::Medium;

$titleText = (string) ($titleData['text'] ?? DEFAULT_TITLE_TEXT);
$titleColor = ((string) ($titleData['color'] ?? '')) ?: DEFAULT_TITLE_COLOR;
$titleFont = Font::tryFrom((string) ($titleData['font'] ?? '')) ?? Font::NotoSansTC;
$titleSize = FontSize::tryFrom((int) ($titleData['fontSize'] ?? 0)) ?? FontSize::Large;
$titleAlignment = resolveAlignment($titleData['alignment'] ?? null);
$titlePosition = resolvePosition($titleData['position'] ?? null, Position::Top);
$titleLineHeight = resolveLineHeight($titleData['lineHeight'] ?? null);

$descriptionText = (string) ($descriptionData['text'] ?? DEFAULT_DESC_TEXT);
$descriptionColor = ((string) ($descriptionData['color'] ?? '')) ?: DEFAULT_DESC_COLOR;
$descriptionFont = Font::tryFrom((string) ($descriptionData['font'] ?? '')) ?? Font::NotoSansTC;
$descriptionSize = FontSize::tryFrom((int) ($descriptionData['fontSize'] ?? 0)) ?? FontSize::Medium;
$descriptionAlignment = resolveAlignment($descriptionData['alignment'] ?? null);
$descriptionPosition = resolvePosition($descriptionData['position'] ?? null, Position::Center);
$descriptionLineHeight = resolveLineHeight($descriptionData['lineHeight'] ?? null);

// Background dispatch. The discriminator $_POST['background'][type] is one of:
//   'solid'    -> background[solid][color]
//   'gradient' -> background[gradient][from], background[gradient][to], background[gradient][direction]
//   'image'    -> background[image][path], background[image][fit],
//                 background[image][opacity], background[image][tint]
$backgroundType = (string) ($backgroundData['type'] ?? 'solid');
if (! in_array($backgroundType, ['solid', 'gradient', 'image'], true)) {
    $backgroundType = 'solid';
}

$solidData = is_array($backgroundData['solid'] ?? null) ? $backgroundData['solid'] : [];
$gradientData = is_array($backgroundData['gradient'] ?? null) ? $backgroundData['gradient'] : [];
$imageData = is_array($backgroundData['image'] ?? null) ? $backgroundData['image'] : [];

$solidColor = ((string) ($solidData['color'] ?? '')) ?: DEFAULT_BG_COLOR;
$gradientFrom = ((string) ($gradientData['from'] ?? '')) ?: DEFAULT_GRADIENT_FROM;
$gradientTo = ((string) ($gradientData['to'] ?? '')) ?: DEFAULT_GRADIENT_TO;
$gradientDirectionName = (string) ($gradientData['direction'] ?? 'Vertical');
$gradientDirection = match ($gradientDirectionName) {
    'Horizontal' => GradientDirection::Horizontal,
    'Diagonal' => GradientDirection::Diagonal,
    default => GradientDirection::Vertical,
};
$imagePath = (string) ($imageData['path'] ?? '');
$imageFitName = (string) ($imageData['fit'] ?? 'Cover');
$imageFit = match ($imageFitName) {
    'Contain' => ImageFit::Contain,
    'Stretch' => ImageFit::Stretch,
    'Tile' => ImageFit::Tile,
    default => ImageFit::Cover,
};
// Clamp opacity to [0.0, 1.0] so an out-of-range POST value (e.g., from a tampered
// form) doesn't trigger the InvalidArgumentException from the Image constructor.
$imageOpacityRaw = $imageData['opacity'] ?? null;
$imageOpacity = $imageOpacityRaw === null || $imageOpacityRaw === '' ? 1.0 : (float) $imageOpacityRaw;
$imageOpacity = max(0.0, min(1.0, $imageOpacity));
$imageTint = ((string) ($imageData['tint'] ?? '')) ?: '#ffffff';

$backgroundError = null;

$generator = new Generator()
    ->size($canvasSize)
    ->margin($canvasMargin);

try {
    match ($backgroundType) {
        'gradient' => $generator->background(new Gradient(
            from: $gradientFrom,
            to: $gradientTo,
            direction: $gradientDirection,
        )),
        'image' => $generator->background(new ImageBackground(
            path: $imagePath,
            fit: $imageFit,
            opacity: $imageOpacity,
            tint: $imageTint,
        )),
        default => $generator->background(new Solid($solidColor)),
    };
} catch (InvalidArgumentException $e) {
    $backgroundError = $e->getMessage();
    $generator->background(new Solid($solidColor !== '' ? $solidColor : DEFAULT_BG_COLOR));
}

if ($titleText !== '') {
    $generator->title(new TextBlock(
        text: $titleText,
        color: $titleColor,
        fontSize: $titleSize,
        font: $titleFont,
        alignment: $titleAlignment,
        lineHeight: $titleLineHeight,
        position: $titlePosition,
    ));
}

if ($descriptionText !== '') {
    $generator->description(new TextBlock(
        text: $descriptionText,
        color: $descriptionColor,
        fontSize: $descriptionSize,
        font: $descriptionFont,
        alignment: $descriptionAlignment,
        lineHeight: $descriptionLineHeight,
        position: $descriptionPosition,
    ));
}

$tmpFile = tempnam(sys_get_temp_dir(), 'preview_').'.png';
$generator->save($tmpFile);
$imageBytes = file_get_contents($tmpFile);
unlink($tmpFile);
$imageDataUri = 'data:image/png;base64,'.base64_encode($imageBytes !== false ? $imageBytes : '');

/**
 * Render a segmented alignment selector (Left / Center / Right) using radio
 * inputs styled as toggle buttons. Used for both title and description.
 */
function renderAlignmentSelector(string $name, Alignment $selected): void
{
    $iconLeft = <<<'SVG'
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
            <line x1="2" y1="4" x2="14" y2="4"/>
            <line x1="2" y1="8" x2="10" y2="8"/>
            <line x1="2" y1="12" x2="12" y2="12"/>
        </svg>
        SVG;
    $iconCenter = <<<'SVG'
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
            <line x1="2" y1="4" x2="14" y2="4"/>
            <line x1="4" y1="8" x2="12" y2="8"/>
            <line x1="3" y1="12" x2="13" y2="12"/>
        </svg>
        SVG;
    $iconRight = <<<'SVG'
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
            <line x1="2" y1="4" x2="14" y2="4"/>
            <line x1="6" y1="8" x2="14" y2="8"/>
            <line x1="4" y1="12" x2="14" y2="12"/>
        </svg>
        SVG;

    $options = [
        'Left' => ['icon' => $iconLeft, 'label' => 'Align left', 'case' => Alignment::Left],
        'Center' => ['icon' => $iconCenter, 'label' => 'Align center', 'case' => Alignment::Center],
        'Right' => ['icon' => $iconRight, 'label' => 'Align right', 'case' => Alignment::Right],
    ];
    ?>

    <span class="align-group" role="radiogroup" aria-label="Alignment">
        <?php foreach ($options as $value => $meta) { ?>
            <label class="align-option align-option--<?= strtolower($value) ?>" title="<?= $meta['label'] ?>">
                <input
                    type="radio"
                    name="<?= htmlspecialchars($name) ?>"
                    value="<?= $value ?>"
                    <?= $meta['case'] === $selected ? 'checked' : '' ?>
                >
                <span aria-hidden="true"><?= $meta['icon'] ?></span>
                <span class="visually-hidden"><?= $meta['label'] ?></span>
            </label>
        <?php } ?>
    </span>

    <?php
}

/**
 * Sibling of renderAlignmentSelector for the vertical axis. The icons reuse
 * the three-horizontal-line glyph language, but the cluster sits in the top,
 * middle, or bottom band of the frame rather than varying line widths.
 */
function renderPositionSelector(string $name, Position $selected): void
{
    $iconTop = <<<'SVG'
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
            <line x1="2" y1="3" x2="14" y2="3"/>
            <line x1="2" y1="6" x2="14" y2="6"/>
            <line x1="2" y1="9" x2="14" y2="9"/>
        </svg>
        SVG;
    $iconMiddle = <<<'SVG'
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
            <line x1="2" y1="5" x2="14" y2="5"/>
            <line x1="2" y1="8" x2="14" y2="8"/>
            <line x1="2" y1="11" x2="14" y2="11"/>
        </svg>
        SVG;
    $iconBottom = <<<'SVG'
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
            <line x1="2" y1="7" x2="14" y2="7"/>
            <line x1="2" y1="10" x2="14" y2="10"/>
            <line x1="2" y1="13" x2="14" y2="13"/>
        </svg>
        SVG;

    $options = [
        'Top' => ['icon' => $iconTop, 'label' => 'Anchor top', 'case' => Position::Top],
        'Center' => ['icon' => $iconMiddle, 'label' => 'Anchor middle', 'case' => Position::Center],
        'Bottom' => ['icon' => $iconBottom, 'label' => 'Anchor bottom', 'case' => Position::Bottom],
    ];
    ?>

    <span class="align-group" role="radiogroup" aria-label="Position">
        <?php
        foreach ($options as $value => $meta) { ?>
            <label class="align-option align-option--<?= strtolower($value) ?>" title="<?= $meta['label'] ?>">
                <input
                    type="radio"
                    name="<?= htmlspecialchars($name) ?>"
                    value="<?= $value ?>"
                    <?= $meta['case'] === $selected ? 'checked' : '' ?>
                >
                <span aria-hidden="true"><?= $meta['icon'] ?></span>
                <span class="visually-hidden"><?= $meta['label'] ?></span>
            </label>
            <?php
        } ?>
    </span>
    <?php
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Preview Playground</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
            color: #1f2937;
        }

        h1 {
            font-size: 1.5rem;
        }

        h2 {
            font-size: 1.125rem;
            margin-top: 2rem;
            color: #4b5563;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        fieldset {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 1rem 1.25rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        legend {
            font-weight: 600;
            padding: 0 0.5rem;
            color: #1f2937;
        }

        label {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            font-size: 0.875rem;
            color: #4b5563;
        }

        input, textarea, select {
            font: inherit;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fff;
        }

        input:focus, textarea:focus, select:focus {
            outline: 2px solid #777bb3;
            outline-offset: -1px;
            border-color: transparent;
        }

        textarea {
            resize: vertical;
            min-height: 4rem;
        }

        .row {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .row > label {
            flex: 1;
            min-width: 7rem;
        }

        .row > label.align-label {
            flex: 0 1 auto;
            min-width: 0;
        }

        .color-input {
            display: flex;
            align-items: stretch;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            overflow: hidden;
        }

        .color-input:focus-within {
            outline: 2px solid #777bb3;
            outline-offset: -1px;
            border-color: transparent;
        }

        .color-swatch {
            width: 2rem;
            flex-shrink: 0;
            background: #ccc;
            border-right: 1px solid #d1d5db;
            cursor: pointer;
            padding: 0;
            border-top: 0;
            border-bottom: 0;
            border-left: 0;
        }

        .color-swatch:focus-visible {
            outline: 2px solid #777bb3;
            outline-offset: -2px;
        }

        .color-swatch[hidden] {
            display: none;
        }

        .color-input input[type="text"], .color-input input:not([type]) {
            border: 0;
            border-radius: 0;
            flex: 1;
            min-width: 0;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        }

        .color-input input:focus {
            outline: 0;
        }

        .color-input.is-invalid .color-swatch {
            opacity: 0.4;
        }

        .color-picker-native {
            position: absolute;
            width: 0;
            height: 0;
            padding: 0;
            border: 0;
            opacity: 0;
            pointer-events: none;
        }

        .color-hint {
            font-size: 0.75rem;
            color: #6b7280;
        }

        button {
            padding: 0.625rem 1.25rem;
            font: inherit;
            background: #777bb3;
            color: #fff;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
            width: fit-content;
        }

        button:hover {
            background: #65689c;
        }

        img {
            max-width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            display: block;
        }

        .bg-tabs {
            display: flex;
            gap: 0.25rem;
            padding: 0.25rem;
            background: #f3f4f6;
            border-radius: 6px;
            width: fit-content;
        }

        .bg-tabs label {
            flex-direction: row;
            align-items: center;
            cursor: pointer;
            padding: 0.375rem 0.875rem;
            border-radius: 4px;
            font-size: 0.875rem;
            color: #4b5563;
            user-select: none;
        }

        .bg-tabs label:hover {
            color: #1f2937;
        }

        .bg-tabs input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .bg-tabs input[type="radio"]:checked + span {
            color: #1f2937;
            font-weight: 600;
        }

        .bg-tabs label:has(input[type="radio"]:checked) {
            background: #fff;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
        }

        .bg-tabs label:focus-within {
            outline: 2px solid #777bb3;
            outline-offset: 1px;
        }

        .bg-mode {
            display: none;
            flex-direction: column;
            gap: 0.75rem;
        }

        .bg-mode.is-active {
            display: flex;
        }

        .gradient-preview {
            width: 100%;
            height: 4rem;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            background: linear-gradient(to bottom, <?= htmlspecialchars($gradientFrom) ?>, <?= htmlspecialchars($gradientTo) ?>);
        }

        .opacity-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fff;
        }

        .opacity-control:focus-within {
            outline: 2px solid #777bb3;
            outline-offset: -1px;
            border-color: transparent;
        }

        .opacity-control input[type="range"] {
            flex: 1;
            min-width: 0;
            padding: 0;
            border: 0;
            background: transparent;
            accent-color: #777bb3;
        }

        .opacity-control input[type="range"]:focus {
            outline: 0;
        }

        .opacity-readout {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 0.8125rem;
            color: #4b5563;
            min-width: 2.5rem;
            text-align: right;
        }

        .align-group {
            display: inline-flex;
            padding: 0.125rem;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            align-self: flex-start;
        }

        .align-group:focus-within {
            outline: 2px solid #777bb3;
            outline-offset: 1px;
        }

        .align-option {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 0.375rem 0.625rem;
            border-radius: 4px;
            color: #6b7280;
            user-select: none;
            line-height: 1;
            transition: background 120ms ease, color 120ms ease;
        }

        .align-option:hover {
            color: #1f2937;
        }

        .align-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
            width: 0;
            height: 0;
        }

        .align-option > span[aria-hidden="true"] {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1rem;
            height: 1rem;
        }

        .align-option svg {
            display: block;
            width: 100%;
            height: 100%;
        }

        .align-option:has(input[type="radio"]:checked) {
            background: #fff;
            color: #1f2937;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.08);
        }

        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .error {
            color: #b91c1c;
            background: #fef2f2;
            border: 1px solid #fecaca;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
<h1>Preview Playground</h1>
<form method="post">
    <fieldset>
        <legend>Canvas</legend>
        <div class="row">
            <label>
                Size
                <select name="canvas[size]">
                    <?php
                    foreach (Size::cases() as $size) { ?>
                        <option value="<?= $size->name ?>" <?= $size === $canvasSize ? 'selected' : '' ?>>
                            <?= $size->name ?> (<?= $size->width() ?> × <?= $size->height() ?>)
                        </option>
                        <?php
                    } ?>
                </select>
            </label>
            <label>
                Margin
                <select name="canvas[margin]">
                    <?php foreach (Margin::cases() as $margin) { ?>
                        <option value="<?= $margin->value ?>" <?= $margin === $canvasMargin ? 'selected' : '' ?>>
                            <?= $margin->name ?> (<?= $margin->value ?>px)
                        </option>
                    <?php } ?>
                </select>
            </label>
        </div>
    </fieldset>

    <fieldset>
        <legend>Background</legend>
        <div class="bg-tabs" role="tablist">
            <?php foreach (['solid' => 'Solid', 'gradient' => 'Gradient', 'image' => 'Image'] as $value => $label) { ?>
                <label>
                    <input type="radio" name="background[type]"
                           value="<?= $value ?>" <?= $backgroundType === $value ? 'checked' : '' ?>
                           data-bg-tab="<?= $value ?>">
                    <span><?= $label ?></span>
                </label>
            <?php } ?>
        </div>

        <?php if ($backgroundError !== null) { ?>
            <div class="error">Background error: <?= htmlspecialchars($backgroundError) ?> (fell back to solid color)
            </div>
        <?php } ?>

        <div class="bg-mode <?= $backgroundType === 'solid' ? 'is-active' : '' ?>" data-bg-panel="solid">
            <label>
                Color <span class="color-hint">(hex like <code>#777bb3</code> or a name: red, green, blue, yellow, orange, white, black)</span>
                <span class="color-input">
                        <button type="button" class="color-swatch"
                                style="background: <?= htmlspecialchars($solidColor) ?>"
                                aria-label="Open color picker"></button>
                        <input type="color" class="color-picker-native" tabindex="-1" aria-hidden="true">
                        <input name="background[solid][color]" value="<?= htmlspecialchars($solidColor) ?>"
                               autocomplete="off">
                    </span>
            </label>
        </div>

        <div class="bg-mode <?= $backgroundType === 'gradient' ? 'is-active' : '' ?>" data-bg-panel="gradient">
            <div class="row">
                <label>
                    From
                    <span class="color-input">
                            <button type="button" class="color-swatch"
                                    style="background: <?= htmlspecialchars($gradientFrom) ?>"
                                    aria-label="Open color picker"></button>
                            <input type="color" class="color-picker-native" tabindex="-1" aria-hidden="true">
                            <input name="background[gradient][from]" value="<?= htmlspecialchars($gradientFrom) ?>"
                                   autocomplete="off" data-gradient="from">
                        </span>
                </label>
                <label>
                    To
                    <span class="color-input">
                            <button type="button" class="color-swatch"
                                    style="background: <?= htmlspecialchars($gradientTo) ?>"
                                    aria-label="Open color picker"></button>
                            <input type="color" class="color-picker-native" tabindex="-1" aria-hidden="true">
                            <input name="background[gradient][to]" value="<?= htmlspecialchars($gradientTo) ?>"
                                   autocomplete="off" data-gradient="to">
                        </span>
                </label>
                <label>
                    Direction
                    <select name="background[gradient][direction]" data-gradient="direction">
                        <?php
                        foreach (GradientDirection::cases() as $direction) { ?>
                            <option
                                value="<?= $direction->name ?>" <?= $direction === $gradientDirection ? 'selected' : '' ?>>
                                <?= $direction->name ?>
                            </option>
                            <?php
                        } ?>
                    </select>
                </label>
            </div>
            <label>
                Live preview
                <span class="gradient-preview" id="gradient-preview" aria-hidden="true"></span>
            </label>
        </div>

        <div class="bg-mode <?= $backgroundType === 'image' ? 'is-active' : '' ?>" data-bg-panel="image">
            <label>
                Path (absolute, server-readable)
                <input name="background[image][path]" value="<?= htmlspecialchars($imagePath) ?>"
                       placeholder="/absolute/path/to/file.png" autocomplete="off">
            </label>
            <div class="row">
                <label>
                    Fit
                    <select name="background[image][fit]">
                        <?php
                        foreach (ImageFit::cases() as $fit) { ?>
                            <option value="<?= $fit->name ?>" <?= $fit === $imageFit ? 'selected' : '' ?>>
                                <?= $fit->name ?>
                            </option>
                            <?php
                        } ?>
                    </select>
                </label>
                <label>
                    Opacity
                    <span class="opacity-control">
                            <input type="range" name="background[image][opacity]" min="0" max="1" step="0.05"
                                   value="<?= htmlspecialchars((string) $imageOpacity) ?>" data-opacity-input
                                   autocomplete="off">
                            <span class="opacity-readout" data-opacity-readout><?= number_format($imageOpacity,
                                2) ?></span>
                        </span>
                </label>
                <label>
                    Tint
                    <span class="color-input">
                            <button type="button" class="color-swatch"
                                    style="background: <?= htmlspecialchars($imageTint) ?>"
                                    aria-label="Open color picker"></button>
                            <input type="color" class="color-picker-native" tabindex="-1" aria-hidden="true">
                            <input name="background[image][tint]" value="<?= htmlspecialchars($imageTint) ?>"
                                   autocomplete="off">
                        </span>
                </label>
            </div>
        </div>
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
                        <button type="button" class="color-swatch"
                                style="background: <?= htmlspecialchars($titleColor) ?>"
                                aria-label="Open color picker"></button>
                        <input type="color" class="color-picker-native" tabindex="-1" aria-hidden="true">
                        <input name="title[color]" value="<?= htmlspecialchars($titleColor) ?>" autocomplete="off">
                    </span>
            </label>
            <label>
                Font
                <select name="title[font]">
                    <?php
                    foreach (Font::cases() as $font) { ?>
                        <option
                            value="<?= htmlspecialchars($font->value) ?>" <?= $font === $titleFont ? 'selected' : '' ?>>
                            <?= $font->name ?>
                        </option>
                        <?php
                    } ?>
                </select>
            </label>
            <label>
                Size
                <select name="title[fontSize]">
                    <?php
                    foreach (FontSize::cases() as $size) { ?>
                        <option value="<?= $size->value ?>" <?= $size === $titleSize ? 'selected' : '' ?>>
                            <?= $size->name ?> (<?= $size->value ?>px)
                        </option>
                        <?php
                    } ?>
                </select>
            </label>
        </div>
        <div class="row">
            <label class="align-label">
                Alignment
                <?php
                renderAlignmentSelector('title[alignment]', $titleAlignment); ?>
            </label>
            <label class="align-label">
                Position
                <?php
                renderPositionSelector('title[position]', $titlePosition); ?>
            </label>
            <label>
                Line height
                <select name="title[lineHeight]">
                    <?php
                    foreach (LineHeight::cases() as $lineHeight) { ?>
                        <option
                            value="<?= $lineHeight->name ?>" <?= $lineHeight === $titleLineHeight ? 'selected' : '' ?>>
                            <?= $lineHeight->name ?> (<?= $lineHeight->multiplier() ?>×)
                        </option>
                        <?php
                    } ?>
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
                        <button type="button" class="color-swatch"
                                style="background: <?= htmlspecialchars($descriptionColor) ?>"
                                aria-label="Open color picker"></button>
                        <input type="color" class="color-picker-native" tabindex="-1" aria-hidden="true">
                        <input name="description[color]" value="<?= htmlspecialchars($descriptionColor) ?>"
                               autocomplete="off">
                    </span>
            </label>
            <label>
                Font
                <select name="description[font]">
                    <?php
                    foreach (Font::cases() as $font) { ?>
                        <option
                            value="<?= htmlspecialchars($font->value) ?>" <?= $font === $descriptionFont ? 'selected' : '' ?>>
                            <?= $font->name ?>
                        </option>
                        <?php
                    } ?>
                </select>
            </label>
            <label>
                Size
                <select name="description[fontSize]">
                    <?php
                    foreach (FontSize::cases() as $size) { ?>
                        <option value="<?= $size->value ?>" <?= $size === $descriptionSize ? 'selected' : '' ?>>
                            <?= $size->name ?> (<?= $size->value ?>px)
                        </option>
                        <?php
                    } ?>
                </select>
            </label>
        </div>
        <div class="row">
            <label class="align-label">
                Alignment
                <?php
                renderAlignmentSelector('description[alignment]', $descriptionAlignment); ?>
            </label>
            <label class="align-label">
                Position
                <?php
                renderPositionSelector('description[position]', $descriptionPosition); ?>
            </label>
            <label>
                Line height
                <select name="description[lineHeight]">
                    <?php
                    foreach (LineHeight::cases() as $lineHeight) { ?>
                        <option
                            value="<?= $lineHeight->name ?>" <?= $lineHeight === $descriptionLineHeight ? 'selected' : '' ?>>
                            <?= $lineHeight->name ?> (<?= $lineHeight->multiplier() ?>×)
                        </option>
                        <?php
                    } ?>
                </select>
            </label>
        </div>
    </fieldset>

    <button type="submit">Generate Preview</button>
</form>

<h2>Result</h2>
<img src="<?= $imageDataUri ?>" alt="Generated preview image">

<script>
    // Named color fallback so swatches and the gradient preview understand the
    // 7 names Converter accepts in addition to hex.
    const NAMED_COLORS = {
        red: '#ff0000', green: '#008000', blue: '#0000ff', yellow: '#ffff00',
        orange: '#ffa500', white: '#ffffff', black: '#000000',
    };

    function normalizeColor(value) {
        const trimmed = value.trim().toLowerCase();
        if (/^#[0-9a-f]{6}$/.test(trimmed)) return trimmed;
        if (NAMED_COLORS[trimmed]) return NAMED_COLORS[trimmed];
        return null;
    }

    // Color swatches next to every color input. The swatch doubles as a
    // launcher for the native OS color picker: clicking it opens a hidden
    // <input type="color"> whose value writes back to the text input. The
    // text input still accepts hex or one of the 7 named colors directly.
    document.querySelectorAll('.color-input').forEach(function (wrapper) {
        const textInput = wrapper.querySelector('input:not([type="color"])');
        const swatch = wrapper.querySelector('.color-swatch');
        const picker = wrapper.querySelector('input[type="color"]');
        if (textInput === null || swatch === null) return;

        function paint(value, markInvalid) {
            const color = normalizeColor(value);
            if (color !== null) {
                swatch.style.background = color;
                wrapper.classList.remove('is-invalid');
                if (picker !== null) picker.value = color;
            } else if (markInvalid) {
                wrapper.classList.add('is-invalid');
            }
        }

        // Initialize the hidden picker so it opens on the current color.
        if (picker !== null) {
            const initial = normalizeColor(textInput.value);
            if (initial !== null) picker.value = initial;
        }

        textInput.addEventListener('input', function () {
            paint(textInput.value, true);
        });

        if (picker !== null) {
            swatch.addEventListener('click', function (event) {
                event.preventDefault();
                picker.click();
            });
            picker.addEventListener('input', function () {
                textInput.value = picker.value;
                paint(picker.value, false);
                // Some downstream listeners (e.g., the gradient preview)
                // are wired to the text input's `input` event.
                textInput.dispatchEvent(new Event('input', {bubbles: true}));
            });
        }
    });

    // Opacity slider readout: show the current 0.00 – 1.00 value next to the
    // range input so users can see the exact ratio as they drag.
    document.querySelectorAll('[data-opacity-input]').forEach(function (input) {
        const readout = input.parentElement.querySelector('[data-opacity-readout]');
        if (readout === null) return;
        input.addEventListener('input', function () {
            readout.textContent = parseFloat(input.value).toFixed(2);
        });
    });

    // Background mode tabs: show only the panel matching the selected radio.
    const tabRadios = document.querySelectorAll('input[name="background[type]"]');
    const panels = document.querySelectorAll('[data-bg-panel]');
    tabRadios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (!radio.checked) return;
            panels.forEach(function (panel) {
                panel.classList.toggle('is-active', panel.dataset.bgPanel === radio.value);
            });
        });
    });

    // Live CSS gradient preview. Maps the library's three directions to the
    // equivalent CSS linear-gradient syntax so what you see is what the PNG
    // will render.
    const DIRECTION_CSS = {
        Vertical: 'to bottom',
        Horizontal: 'to right',
        Diagonal: 'to bottom right',
    };
    const gradientPreview = document.getElementById('gradient-preview');
    const gradientFromInput = document.querySelector('input[data-gradient="from"]');
    const gradientToInput = document.querySelector('input[data-gradient="to"]');
    const gradientDirSelect = document.querySelector('select[data-gradient="direction"]');

    function updateGradientPreview() {
        const from = normalizeColor(gradientFromInput.value) ?? '#000000';
        const to = normalizeColor(gradientToInput.value) ?? '#ffffff';
        const dir = DIRECTION_CSS[gradientDirSelect.value] ?? 'to bottom';
        gradientPreview.style.background = `linear-gradient(${dir}, ${from}, ${to})`;
    }

    gradientFromInput.addEventListener('input', updateGradientPreview);
    gradientToInput.addEventListener('input', updateGradientPreview);
    gradientDirSelect.addEventListener('change', updateGradientPreview);
    updateGradientPreview();
</script>
</body>
</html>
