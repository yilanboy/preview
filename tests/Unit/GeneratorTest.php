<?php

use Yilanboy\Preview\Generator;

it('throws on non-positive width', function () {
    new Generator()->size(width: 0, height: 600);
})->throws(InvalidArgumentException::class);

it('throws on non-positive height', function () {
    new Generator()->size(width: 1200, height: -1);
})->throws(InvalidArgumentException::class);
