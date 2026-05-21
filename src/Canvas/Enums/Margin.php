<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Canvas\Enums;

enum Margin: int
{
    case None = 0;
    case Small = 30;
    case Medium = 60;
    case Large = 90;
    case ExtraLarge = 120;
}
