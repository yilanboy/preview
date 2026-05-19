<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Image\Background;

use GdImage;
use Yilanboy\Preview\Color\Converter;

interface Background
{
    public function apply(GdImage $image, int $width, int $height, Converter $converter): void;
}
