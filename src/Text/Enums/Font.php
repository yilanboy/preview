<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Text\Enums;

enum Font: string
{
    case Inter = 'inter.ttf';
    case InterMedium = 'inter-medium.ttf';
    case Roboto = 'roboto.ttf';
    case RobotoMedium = 'roboto-medium.ttf';
    case JetBrainsMono = 'jetbrains-mono.ttf';
    case JetBrainsMonoMedium = 'jetbrains-mono-medium.ttf';
    case NotoSans = 'noto-sans.ttf';
    case NotoSansMedium = 'noto-sans-medium.ttf';
    case NotoSansSC = 'noto-sans-sc.ttf';
    case NotoSansSCMedium = 'noto-sans-sc-medium.ttf';
    case NotoSansTC = 'noto-sans-tc.ttf';
    case NotoSansTCMedium = 'noto-sans-tc-medium.ttf';
    case NotoSansJP = 'noto-sans-jp.ttf';
    case NotoSansJPMedium = 'noto-sans-jp-medium.ttf';

    public function path(): string
    {
        return __DIR__.'/../../../fonts/'.$this->value;
    }
}
