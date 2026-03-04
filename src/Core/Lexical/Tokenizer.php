<?php

declare(strict_types=1);

namespace DLParse\Core\Lexical;

use DLParse\Exceptions\TokenizerException;

abstract class Tokenizer extends Normalizer {

    /**
     * Tokens capturados
     *
     * @var array
     */
    private array $tokens = [];

    public function __construct(string $content, bool $collapse = false) {
        parent::__construct($content, $collapse);
    }
}