<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Text\Enums;

enum Font: string
{
    case NotoSansTC = 'noto-sans-tc.ttf';
    case NotoSans = 'noto-sans.ttf';
    case Inter = 'inter.ttf';
    case Roboto = 'roboto.ttf';

    public function path(): string
    {
        return __DIR__.'/../../../fonts/'.$this->value;
    }
}
