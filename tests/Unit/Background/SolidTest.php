<?php

use Yilanboy\Preview\Canvas\Background\Solid;

it('stores the color verbatim', function () {
    $solid = new Solid('#10b981');

    expect($solid->color)->toBe('#10b981');
});
