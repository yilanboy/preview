<?php

include_once __DIR__.'/../vendor/autoload.php';

use Yilanboy\Preview\Image\Builder;
use Yilanboy\Preview\Image\Enums\FontSize;
use Yilanboy\Preview\Image\TextBlock;

new Builder()
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
    ->save('preview.png');
