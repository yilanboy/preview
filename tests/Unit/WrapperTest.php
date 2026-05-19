<?php

use Yilanboy\Preview\Image\Writer;

it('can wrap the english sentence into words', function () {
    $writer = new Writer;

    expect($writer->splitStringToArray('Hello World!'))
        ->toBe(['Hello', ' ', 'World', '!']);
});

it('can wrap the chinese sentence into character', function () {
    $writer = new Writer;

    expect($writer->splitStringToArray('你好世界！'))
        ->toBe(['你', '好', '世', '界', '！']);
});

it('can wrap sentence that mix english and chinese', function () {
    $writer = new Writer;

    expect($writer->splitStringToArray('Hello 世界！'))
        ->toBe(['Hello', ' ', '世', '界', '！']);
});
