# Changelog

All the changes to `preview` will be documented in this file.

## 2.0.0-beta.2 - 2026-06-11

This is a pre-release intended for testing. Install it with `composer require yilanboy/preview:^2.0@beta`. APIs may
still change before the stable `2.0.0` release.

### Added

- `Generator::dimensions(int $width, int $height)` to set a custom canvas size instead of a `Size` preset. Throws an
  `InvalidInput` exception when either value is below `1`.
- `TextBlock` now accepts a custom integer font size in pixels (`FontSize|int`) instead of only `FontSize` presets.
  Throws an `InvalidInput` exception when the value is below `1`.
- Package-level exceptions in the new `Exceptions` namespace: invalid input now throws `InvalidInput` and render-time
  GD failures throw `RenderFailure`, both implementing the `PreviewException` marker interface. Backward compatible:
  they extend the SPL `InvalidArgumentException` / `RuntimeException` the library threw before, so existing catch
  blocks keep working.

### Changed

- Text lines are measured once during wrapping: `Surveyor::wrapText()` now returns `MeasuredLine` objects (text plus
  pixel width) instead of plain strings, and placement reuses the stored widths rather than re-measuring each line
  with `imagettfbbox`.
- `Image` backgrounds decode the source file once and cache the bitmap, so rendering many canvases from the same
  background no longer re-reads and re-decodes the file on every draw.

## 2.0.0-beta.1 - 2026-06-09

This is a pre-release intended for testing. Install it with `composer require yilanboy/preview:^2.0@beta`. APIs may
still change before the stable `2.0.0` release.

### Breaking

- Raise the minimum PHP requirement from `^8.2` to `^8.4`.
- Remove `Image\Builder` and replace it with a new `Generator` class as the main entry point. This is an API rename and
  is not backward compatible.
- Replace `Color\Converter` with a top-level `ColorConverter`. Its methods are now static and dependency injection has
  been removed.
- Remove the `backgroundColor` shortcut. Backgrounds are now configured via dedicated background classes.
- Move background interfaces into a `Contracts` namespace (`Contracts\Background`).
- Rework text handling: a new `TextBlock` value object (text, color, font, font size, alignment, line height, position)
  and an internal `TextPlacer` layout engine replace the old inline text handling.

### Added

- Backgrounds in the new `Canvas\Background` namespace:
    - `Solid` for a single color.
    - `Gradient` with a `GradientDirection` enum (`Vertical`, `Horizontal`, `Diagonal`). Gradient rendering uses line
      drawing instead of pixel-by-pixel loops for better performance.
    - `Image` with an `ImageFit` enum (`Cover`, `Contain`, `Stretch`, `Tile`), configurable `opacity`, and a `tint`
      color shown through partial transparency.
    - `ImageValidator` to validate background image files.
- Text and fonts in the `Text` namespace:
    - `TextBlock` supporting a title and description that stack without overlapping when they share the same position.
    - Enums: `Position` (`Top`, `Center`, `Bottom`), `Alignment` (`Left`, `Center`, `Right`), `FontSize`, `LineHeight`,
      and `Font` for the bundled fonts.
    - Support for custom TrueType (`.ttf`) font paths, validated by `FontValidator` (non-TTF / OpenType files are
      rejected).
    - Multi-language text rendering for Traditional Chinese, Simplified Chinese, and Japanese, with appropriate word and
      character wrapping.
- Generator presets and output:
    - `Size` enum with dimension presets.
    - `Margin` enum with margin presets.
    - `Format` enum for image output format handling, integrated with `Generator`.
- Color validation:
    - `ColorConverter::isValidColor()` accepts a known color name (for example `white`, `black`) or a valid hex code.
    - Color validation is enforced at construction across all color-accepting types: `Solid`, `Gradient` (both `from`
      and `to`), `Image` (`tint`), and `TextBlock` (`color`). They throw `InvalidArgumentException` on invalid colors.
- Snapshot/cluster-based image comparison testing, including multi-language text fixtures.

## 1.0.0 - 2024-12-21

- initial release

## 1.0.1 - 2024-12-21

- remove default text
- change default font size
