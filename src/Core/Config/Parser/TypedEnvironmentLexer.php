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

use DLParse\Core\Config\Parser\Contracts\LexicalMaps;
use DLParse\Core\Config\Parser\Enums\ScannerAction;
use DLParse\Core\Config\Parser\Enums\TokenTerminationState;
use DLParse\Core\Config\Parser\Enums\TokenType;
use DLParse\Core\Lexical\Normalizer;

/**
 * Generador de tokens base para DL Typed Environment
 * 
 * @package DLParse\Core\Config\Parser
 * 
 * @version v0.0.1 (release)
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @license MIT
 */
abstract class TypedEnvironmentLexer extends Normalizer implements LexicalMaps {

    /**
     * Token inicial esperado
     *
     * @var TokenType
     */
    private TokenType $tokentype = TokenType::IDENTIFIER;

    /**
     * Indica cuál es el byte de terminación del token
     * 
     * @var TokenTerminationState $token_termination_state
     */
    private TokenTerminationState $token_termination_state = TokenTerminationState::NONE;

    /** @var Lexeme[] tokens */
    private static array $tokens = [];

    // private string 

    /**
     * Contenido a ser cargado
     *
     * @var string
     */
    private static string $input;

    /** @var int $line */
    private static int $line = 1;

    /** @var int $column */
    private static int $column = 1;

    /**
     * Offset o cursor actual del byte
     *
     * @var integer
     */
    private static int $offset = 0;

    /**
     * Longitud del lexema actualmente en construcción.
     *
     * Representa la cantidad de símbolos consumidos desde el inicio del
     * reconocimiento del token actual. Este contador se incrementa conforme
     * el autómata avanza sobre la entrada y forma parte del proceso de
     * acumulación del lexema.
     *
     * Su valor se reinicia inmediatamente después de emitir un token,
     * marcando el inicio de un nuevo proceso de reconocimiento léxico.
     *
     * En términos formales, corresponde a:
     * length = |lexeme|
     *
     * donde `lexeme` es la secuencia de símbolos reconocida para el token en curso
     *
     * @var int
     */
    private static int $length = 0;

    /**
     * Acción actual del scanner en la iteración del autómata.
     *
     * Define la instrucción operativa (γ(q, a)) que se aplicará sobre el byte
     * evaluado en la iteración actual. Esta acción determina cómo afecta dicho
     * byte al rango del lexema en construcción o al flujo de emisión de tokens.
     *
     * Semántica:
     * - SKIP   → el byte es ignorado y no forma parte del lexema
     * - EXPECT → valida una secuencia obligatoria mediante lookahead
     * - APPEND → extiende el rango del lexema actual
     * - PROBE  → valida opcionalmente una secuencia sin forzar error
     * - EMIT   → finaliza y emite el lexema actual como token
     *
     * Esta propiedad es recalculada en cada iteración en función del estado
     * del DFA y del byte actual, y consumida inmediatamente por el loop
     * principal del scanner.
     *
     * @var ScannerAction
     */
    private ScannerAction $scanner_action = ScannerAction::SKIP;

    public function __construct(string $content, bool $normalize = false) {
        parent::__construct($content, $normalize);
        $this->load_content();
    }

    /**
     * Carga el contenido a ser parseado
     *
     * @return void
     */
    private function load_content(): void {
        self::$input = $this->get_normalized_content();
    }

    /**
     * Mueve el cursor un paso hacia adelante, pero debe utilizarse solo en casos
     * muy específicos para evitar pérdida de rendimiento.
     * 
     *
     * @return void
     */
    private function move_cursor(): void {
        self::$offset++;
        self::$column++;
    }

    /**
     * Mueve el puntero al siguiente byte, pero restableciendo la columna a su posición original
     * e incrementando el número de línea.
     * 
     * Este método solo debe utilizar cuando se encuentre un salto de línea.
     *
     * @return void
     */
    private function move_line(): void {
        self::$offset++;
        self::$line++;
        self::$column = 1;
    }

    /**
     * Aplica un salto compensado al cursor para superar un delimitador.
     * 
     * @param int $cursor_stride Ajuste de pasos (length - 1).
     * @return void
     */
    private function apply_stride(int $cursor_stride): void {
        self::$offset += $cursor_stride;
        self::$column += $cursor_stride;    
    }

    /**
     * Punto de entrada para el escaneo del token
     *
     * @return void
     */
    public function scan(): void {

        while (self::$offset < self::$processed_content_size) {
            /** @var non-empty-string $byte */
            $byte = self::$input[self::$offset];

            /** @var non-empty-string|null $peek */
            $peek = self::$input[self::$offset + 1] ?? null;

            /** @var boolean $is_break_line */
            $is_break_line = (self::CR === $byte && self::LF === $peek) || $byte === self::$break_line;

            if (self::HASH_LINE_COMMENT === $byte) {
                $this->tokentype = TokenType::HASH_LINE_COMMENT;
                $this->scanner_action = ScannerAction::APPEND;

                $this->emit_token_hash_comment();
            }

            if (self::SLASH_MARKER === $byte) {
                $this->scanner_action = ScannerAction::EXPECT;

                /** Aquí es donde se va a definir si el comentario es de línea o de bloque */
                $this->emit_token_comment();
            }

            self::$offset++;
            self::$column++;

            if ($is_break_line) {
                self::$column = 1;
                self::$line++;
            }
        }

        # Este print es temporal para evaluar los tokens producidos para el lexema.
        print_r(self::$tokens);
    }

    /**
     * Emite un token de comentario de línea que empiecen por `#`
     *
     * @return void
     */
    private function emit_token_hash_comment(): void {
        if (!$this->is_append())
            return;

        /** @var int  */
        $start_offset = self::$offset;

        /** @var int $start_column */
        $start_column = self::$column;

        /** @var false|int $next */
        $next = strpos(
            haystack: self::$input,
            needle: self::$break_line,
            offset: $start_offset
        );

        if ($next === false) {
            self::$length = self::$processed_content_size - $start_offset;
            $this->emit_token($start_offset, $start_column);
            self::$offset = self::$processed_content_size;
            return;
        }

        self::$length = $next - $start_offset;
        $this->emit_token($start_offset, $start_column);

        self::$offset = $next;
        self::$line++;
        self::$column = 1;
    }

    /**
     * Emite un token de comentario de bloque o de línea, según lo que se encuentre en el siguiente byte
     *
     * @return void
     */
    private function emit_token_comment(): void {
        if (!$this->is_expect())
            return;

        /** @var non-empty-string $byte */
        $byte = self::$input[self::$offset + 1] ?? null;

        if ($byte === self::SLASH_MARKER || $byte === self::ASTERISK) {
            $this->set_append();
        }

        if ($byte === self::ASTERISK) {
            $this->set_tokentype_block_comment();
            $this->emit_token_block_comment();
        }

        if ($byte === self::SLASH_MARKER) {
            $this->set_tokentype_line_comment();
            $this->emit_token_line_comment();
        }
    }

    /**
     * Emite token de comentarios de una sola línea que empiecen por `//`
     *
     * @return void
     */
    private function emit_token_line_comment(): void {
        if (!$this->is_append()) {
            return;
        }

        /** @var int $start_columnb */
        $start_column = self::$column;

        /** @var int $start_offset */
        $start_offset = self::$offset;

        $this->string_position(self::$break_line, true);
        $this->emit_token($start_offset, $start_column);
    }

    /**
     * Emite token de comentarios de múltiples líneas.
     *
     * @return void
     */
    private function emit_token_block_comment(): void {
        if (!$this->is_append()) {
            return;
        }

        /** @var int|null $offset */
        $offset = self::$offset;

        /** @var int|null $column */
        $column = self::$column;

        $this->string_position(self::BLOCK_COMMENT_END, false, true);
        $this->emit_token($offset, $column);
    }

    /**
     * Emite un token excluyendo el byte de terminación actual.
     *
     * El lexema se extrae desde la posición original donde comenzó la captura
     * hasta la longitud acumulada, la cual no debe incluir el carácter 
     * que disparó esta llamada.
     *
     * @param int &$offset Referencia al punto de inicio del token. Se limpia tras emitir.
     * @param int|null &$column Referencia a la columna de inicio. Se limpia tras emitir.
     */
    private function emit_token(int &$offset, ?int &$column): void {
        if ($offset === null || $column === null)
            return;

        self::$tokens[] = new Lexeme(
            lexeme_content: \substr(self::$input, $offset, self::$length),
            tokentype: $this->tokentype,
            line: self::$line,
            column: $column,
            offset: $offset,
            length: self::$length
        );

        $column = null;

        $this->set_initial_state();
    }

    /**
     * Verifica si el scanner está en modo de captura de bytes.
     *
     * @return boolean
     */
    private function is_append(): bool {
        return $this->scanner_action === ScannerAction::APPEND;
    }

    /**
     * Verifica si el autómata está en modo de espera de un byte adicional
     * para determinar el comportamiento.
     *
     * @return boolean
     */
    private function is_expect(): bool {
        return $this->scanner_action === ScannerAction::EXPECT;
    }

    /**
     * Establece el autómata en modo captura.
     *
     * @return void
     */
    private function set_append(): void {
        $this->scanner_action = ScannerAction::APPEND;
    }

    /**
     * Establece el comportamiento del autómata en modo de espera del siguiente
     * byte para determinar la acción.
     *
     * @return void
     */
    private function set_expect(): void {
        $this->scanner_action = ScannerAction::EXPECT;
    }

    /**
     * Establece el comportamiento del autómata a modo de omisión
     *
     * @return void
     */
    private function set_skip(): void {
        $this->scanner_action = ScannerAction::SKIP;
    }

    /**
     * Establece el tipo de token como identificador de variable
     *
     * @return void
     */
    private function set_tokentype_identifier(): void {
        $this->tokentype = TokenType::IDENTIFIER;
    }

    /**
     * Establece el tipo de token como separador de definición de tipo.
     *
     * @return void
     */
    private function set_tokentype_colon(): void {
        $this->tokentype = TokenType::COLON;
    }

    /**
     * Establece el tipo de token como tipos.
     *
     * @return void
     */
    private function set_tokentype_type(): void {
        $this->tokentype = TokenType::TYPE;
    }

    /**
     * Establece el tipo de token como operador de asignación.
     *
     * @return void
     */
    private function set_tokentype_assign(): void {
        $this->tokentype = TokenType::ASSIGN;
    }

    /**
     * Establece el tipo de token como token de valor
     *
     * @return void
     */
    private function set_tokentype_value(): void {
        $this->tokentype = TokenType::VALUE;
    }

    /**
     * Establece el tipo de token como token de comentario en línea
     * con doble barra diagonal.
     *
     * @return void
     */
    private function set_tokentype_line_comment(): void {
        $this->tokentype = TokenType::LINE_COMMENT;
    }

    /**
     * Establece el tipo de token como token de comentario que empieza por almoadilla
     *
     * @return void
     */
    private function set_tokentype_hash_comment(): void {
        $this->tokentype = TokenType::HASH_LINE_COMMENT;
    }

    /**
     * Establece el tipo de token como token de comentario de múltiples líneas o de bloque.
     *
     * @return void
     */
    private function set_tokentype_block_comment(): void {
        $this->tokentype = TokenType::BLOCK_COMMENT;
    }

    /**
     * Establece el estado inicial del autómata tras la emisión del token
     *
     * @return void
     */
    private function set_initial_state(): void {
        self::$length = 0;
        $this->scanner_action = ScannerAction::SKIP;
        $this->token_termination_state = TokenTerminationState::NONE;
    }

    /**
     * Mueve el cursor del autómata en función del criterio de búsqueda
     *
     * @param string $search Criterio de búsqueda
     * @param boolean $with_strpos Indica si debe utilizarse el método `strpos(...)`. Por defecto es `false`.
     * @param boolean $include_delimiter Indica si se debe incluir el delimitador como parte de los bytes a incluir
     * @return void
     */
    private function string_position(string $search, bool $with_strpos = false, bool $include_delimiter = false): void {
        /** @var int|null $offset */
        $offset = null;

        /** @var int|null $start_offset */
        $start_offset = self::$offset;

        /**
         * Longitud de bytes de la búsqueda
         * 
         * @var int $search_length
         */
        $search_length = \strlen($search);

        /**
         * Salto relativo para compensar el avance natural del bucle.
         * Evita errores "off-by-one" tras hallar el delimitador.
         * 
         * @var int $cursor_stride
         */
        $cursor_stride = $search_length - 1;

        if ($with_strpos && !$include_delimiter) {
            $offset = \strpos(self::$input, $search, self::$offset);

            self::$offset = $offset !== FALSE
                ? $offset
                : self::$processed_content_size;

            self::$length = self::$offset - $start_offset;
            $this->apply_stride($cursor_stride);

            return;
        }

        while (self::$offset < self::$processed_content_size) {

            /** @var non-empty-string $byte */
            $byte = self::$input[self::$offset];

            /** @var non-empty-string|null $peek Alguna documentación */
            $peek = self::$input[self::$offset + 1] ?? null;

            /** @var boolean $is_break_line */
            $is_break_line = (self::CR === $byte && self::LF === $peek) || $byte === self::$break_line;

            if ($byte === self::BACK_SLASH) {
                $this->move_cursor();
                continue;
            }

            if ($byte === $search[0]) {

                if (\substr(self::$input, self::$offset, $search_length) === $search) {

                    if ($include_delimiter) {
                        $this->included_delimiter($start_offset, $search_length);
                        break;
                    }

                    self::$length = self::$offset - $start_offset;
                    $this->apply_stride($cursor_stride);
                    break;
                }
            }

            self::$offset++;
            self::$column++;
            
            if ($is_break_line) {
                self::$line++;
                self::$column = 1;
            }
        }
    }

    /**
     * Mueve el cursor hasta una posición que permita incluir también el delimitador
     *
     * @param integer $start_offset Offset desde donde comienza el lexema del token.
     * @param integer $search_length Longitud de bytes del lexema.
     * @return void
     */
    private function included_delimiter(int $start_offset, int $search_length): void {
        self::$offset += $search_length;
        self::$column += $search_length;
        self::$length = self::$offset - $start_offset;
    }
}