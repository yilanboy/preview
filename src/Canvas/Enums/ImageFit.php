<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Canvas\Enums;

enum ImageFit
{
    case Cover;
    case Contain;
    case Stretch;
    case Tile;
}
