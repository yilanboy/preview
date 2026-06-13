<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Canvas;

use GdImage;

interface Background
{
    public function draw(GdImage $image, int $width, int $height): void;
}
