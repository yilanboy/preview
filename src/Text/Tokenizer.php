<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Text;

final readonly class Tokenizer
{
    /**
     * Tokenize a string into the smallest units at which a line may wrap.
     *
     * Each token is one unbreakable chunk for wrapText():
     *  - Latin letters and digits group into whole words (wrap between words).
     *  - Chinese, Japanese (Kanji, Hiragana, Katakana) split per character,
     *    since these scripts may wrap between any two characters.
     *  - Whitespace and other symbols each become a single token.
     *
     * @return array<string>
     */
    public function splitStringToArray(string $input): array
    {
        $matches = null;

        preg_match_all('/[\p{Han}\p{Hiragana}\p{Katakana}]|[a-zA-Z0-9]+|\s|[^\p{Han}\p{Hiragana}\p{Katakana}\s\w]/u', $input, $matches);

        return $matches[0];
    }
}
