<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Canvas\Background;

use GdImage;

interface Background
{

    public function draw(GdImage $image, int $width, int $height): void;

}
