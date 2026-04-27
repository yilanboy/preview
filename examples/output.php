<?php

include_once __DIR__.'/../vendor/autoload.php';

use Yilanboy\Preview\Image\Builder;

new Builder()
    ->size(width: 1200, height: 600)
    ->backgroundColor('#777bb3')
    ->header(text: 'Preview', color: 'white', fontSize: 75)
    ->title(text: 'A simple PHP package to create preview image', color: 'white', fontSize: 50)
    ->output();
