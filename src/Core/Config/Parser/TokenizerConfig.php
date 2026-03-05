<?php

/**
 * Copyright (c) 2026 David E Luna M
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @license MIT
 */

declare(strict_types=1);

namespace DLParse\Core\Config\Parser;

use DLParse\Core\Lexical\Normalizer;
use DLParse\Exceptions\TokenizerException;

abstract class TokenizerConfig extends Normalizer {

    /** @var string[] Identificadores */
    private array $identifiers = [];

    private readonly string $content;

    /** @var int $line */
    private int $line = 1;

    /** @var int $column */
    private int $column = 1;

    public function __construct(string $content, bool $collapse = true) {
        parent::__construct($content, $collapse);
        $this->load_content();
    }

    private function load_content(): void {
        $this->content = $this->get_normalized_content();
    }

    /**
     * Consume un posible terminador de línea dentro del flujo de análisis léxico.
     *
     * Valida que el lexema recibido esté compuesto por exactamente
     * un byte. Si la longitud no cumple con esta precondición, se lanza una
     * TokenizerException.
     *
     * En caso de que el byte corresponda al carácter definido como terminador
     * de línea (self::BREAK_LINE), el contador interno de líneas es incrementado.
     * Si no corresponde a un salto de línea válido, el método finaliza sin
     * efectos colaterales.
     *
     * @param string $line Byte candidato a terminador de línea.
     *
     * @throws TokenizerException Si la longitud del argumento es distinta de 1 byte.
     *
     * @return void
     */
    private function consume_line(string $line): void {
        /** @var int $length */
        $length = \strlen($line);

        if ($length !== 1) {
            throw new TokenizerException(
                \sprintf(
                    'Longitud inválida del terminador de línea: se esperaba exactamente 1 byte, se recibieron %d bytes.',
                    $length
                )
            );
        }

        /** @var boolean $break_line */
        $break_line = $line === self::BREAK_LINE;

        if (!$break_line) {
            return;
        }

        ++$this->line;
        $this->reset_column();
    }

    /**
     * Consume una unidad lógica de columna dentro del flujo de bytes.
     *
     * Esta operación representa el avance horizontal del cursor
     * dentro de la línea actual, permitiendo mantener el conteo
     * de posición columnar durante el proceso de análisis léxico.
     *
     * Semánticamente:
     * - Actualiza el estado interno asociado a la columna.
     * - Refleja el desplazamiento producido por la lectura de un byte.
     * - Puede reiniciarse implícitamente ante un salto de línea
     *   (según la lógica que implemente la clase concreta).
     *
     * Esta función no retorna valor y forma parte del mecanismo
     * interno de control posicional del normalizador.
     *
     * @return void
     */
    private function consume_column(): void {
        ++$this->column;
    }

    /**
     * Reinicia la columna a su estado inicial
     *
     * @return void
     */
    private function reset_column(): void {
        $this->column = 1;
    }

    
}