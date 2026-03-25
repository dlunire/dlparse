<?php

/**
 * Copyright (c) 2026 David E Luna M
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to do so, subject to the conditions.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND.
 *
 * @license MIT
 */

declare(strict_types=1);

namespace DLParse\Exceptions;

use Exception;

/**
 * LexicalException
 *
 * Se lanza cuando ocurre un error durante la fase de análisis léxico
 * (tokenización) del input.
 *
 * Representa fallos en el reconocimiento de símbolos o secuencias
 * inválidas en la cinta de entrada, antes de cualquier validación
 * sintáctica o semántica.
 *
 * Ejemplos:
 * - Byte inesperado en una secuencia multi-byte (EXPECT fallido)
 * - Secuencia inválida de comentario (`/` no seguido de `/` o `*`)
 * - Carácter fuera del alfabeto definido
 * - Token incompleto o mal formado al finalizar la entrada
 *
 * Uso típico:
 * - Autómatas finitos deterministas (DFA)
 * - Scanners / Lexers
 * - Transductores léxicos con validación estructural
 *
 * @package DLParse\Exceptions
 * @version v0.0.1
 * @license MIT
 * @author David E Luna M
 * @copyright Copyright (c) 2026 David E Luna M
 */
final class LexicalException extends Exception {

    /**
     * @param string          $message  Mensaje descriptivo del error léxico
     * @param int             $code     Código de error (0 por defecto)
     * @param \Throwable|null $previous Excepción previa (encadenamiento)
     */
    public function __construct(
        string $message = 'Error durante el análisis léxico.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}