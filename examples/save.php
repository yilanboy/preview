<?php

include_once __DIR__.'/../vendor/autoload.php';

use Yilanboy\Preview\Canvas\Background\Solid;
use Yilanboy\Preview\Generator;
use Yilanboy\Preview\Text\Enums\FontSize;
use Yilanboy\Preview\Text\TextBlock;

new Generator()
    ->size(width: 1200, height: 600)
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
    ->save('preview.png');
