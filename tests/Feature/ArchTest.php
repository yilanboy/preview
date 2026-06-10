<?php

use Yilanboy\Preview\Exceptions\InvalidInput;
use Yilanboy\Preview\Exceptions\PreviewException;
use Yilanboy\Preview\Exceptions\RenderFailure;

arch()->preset()->php();

arch()
    ->expect('Yilanboy\Preview')
    ->toUseStrictTypes();

arch('every exception implements the PreviewException marker')
    ->expect('Yilanboy\Preview\Exceptions')
    ->toImplement(PreviewException::class)
    ->ignoring(PreviewException::class);

arch('InvalidInput stays catchable as an SPL InvalidArgumentException')
    ->expect(InvalidInput::class)
    ->toExtend(InvalidArgumentException::class);

arch('RenderFailure stays catchable as an SPL RuntimeException')
    ->expect(RenderFailure::class)
    ->toExtend(RuntimeException::class);
