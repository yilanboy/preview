<?php

use Yilanboy\Preview\Text\Tokenizer;

it('can wrap the English sentence into words', function () {
    $tokenizer = new Tokenizer;

    expect($tokenizer->splitStringToArray('Hello World!'))
        ->toBe(['Hello', ' ', 'World', '!']);
});

it('can wrap the Chinese sentence into character', function () {
    $tokenizer = new Tokenizer;

    expect($tokenizer->splitStringToArray('你好世界！'))
        ->toBe(['你', '好', '世', '界', '！']);
});

it('can wrap the Japanese sentence into character', function () {
    $tokenizer = new Tokenizer;

    expect($tokenizer->splitStringToArray('こんにちは世界！'))
        ->toBe(['こ', 'ん', 'に', 'ち', 'は', '世', '界', '！']);
});

it('can wrap sentence that mix English and Chinese', function () {
    $tokenizer = new Tokenizer;

    expect($tokenizer->splitStringToArray('Hello 世界！'))
        ->toBe(['Hello', ' ', '世', '界', '！']);
});
