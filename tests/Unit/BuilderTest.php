<?php

use Yilanboy\Preview\Image\Builder;

it('throws on non-positive width', function () {
    (new Builder)->size(width: 0, height: 600);
})->throws(InvalidArgumentException::class);

it('throws on non-positive height', function () {
    (new Builder)->size(width: 1200, height: -1);
})->throws(InvalidArgumentException::class);
