<?php

include_once __DIR__.'/../vendor/autoload.php';

use Yilanboy\Preview\Canvas\Enums\Format;
use Yilanboy\Preview\Canvas\Enums\Size;
use Yilanboy\Preview\Canvas\Solid;
use Yilanboy\Preview\Generator;
use Yilanboy\Preview\Text\Enums\Font;
use Yilanboy\Preview\Text\Enums\FontSize;
use Yilanboy\Preview\Text\TextBlock;

new Generator()
    ->size(Size::OpenGraph)
    ->format(Format::PNG)
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
    ->save('images/preview.png');
