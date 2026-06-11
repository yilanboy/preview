<?php

use Yilanboy\Preview\Text\Tokenizer;

it('can wrap the English sentence into words', function () {
    $tokenizer = new Tokenizer;

    expect($tokenizer->tokenize('Hello World!'))
        ->toBe(['Hello', ' ', 'World', '!']);
});

it('can wrap the Chinese sentence into character', function () {
    $tokenizer = new Tokenizer;

    expect($tokenizer->tokenize('你好世界！'))
        ->toBe(['你', '好', '世', '界', '！']);
});

it('can wrap the Japanese sentence into character', function () {
    $tokenizer = new Tokenizer;

    expect($tokenizer->tokenize('こんにちは世界！'))
        ->toBe(['こ', 'ん', 'に', 'ち', 'は', '世', '界', '！']);
});

it('can wrap sentence that mix English and Chinese', function () {
    $tokenizer = new Tokenizer;

    expect($tokenizer->tokenize('Hello 世界！'))
        ->toBe(['Hello', ' ', '世', '界', '！']);
});

it('normalizes CRLF and bare CR newlines to LF', function () {
    $tokenizer = new Tokenizer;

    expect($tokenizer->tokenize("a\r\nb\rc"))
        ->toBe(['a', "\n", 'b', "\n", 'c']);
});

it('trims surrounding whitespace and newlines', function () {
    $tokenizer = new Tokenizer;

    expect($tokenizer->tokenize("\n  Hello  \n"))
        ->toBe(['Hello']);
});
