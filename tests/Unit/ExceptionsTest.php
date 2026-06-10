<?php

use Yilanboy\Preview\Exceptions\InvalidInput;
use Yilanboy\Preview\Exceptions\PreviewException;
use Yilanboy\Preview\Exceptions\RenderFailure;

it('keeps InvalidInput catchable as an Standard PHP Library (SPL) InvalidArgumentException',
    function () {
        $exception = new InvalidInput('bad input');

        expect($exception)->toBeInstanceOf(InvalidArgumentException::class)
            ->and($exception)->toBeInstanceOf(PreviewException::class);
    });

it('keeps RenderFailure catchable as an SPL RuntimeException',
    function () {
        $exception = new RenderFailure('render failed');

        expect($exception)->toBeInstanceOf(RuntimeException::class)
            ->and($exception)->toBeInstanceOf(PreviewException::class);
    });
