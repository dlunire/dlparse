<?php

declare(strict_types=1);

namespace DLParse\Core\Config\Parser;

use DLParse\Exceptions\TokenizerException;

/**
 * Copyright (c) 2026 David E Luna M
 * Licensed under the MIT License. See LICENSE file for details.
 *
 * Class Lexeme
 *
 * Representa la configuración estructural de un token generado durante la fase de análisis léxico.
 *
 * Esta entidad actúa como contenedor inmutable (write-once) para los metadatos asociados
 * a un token, incluyendo su lexema, tipo sintáctico y posición dentro del flujo de entrada.
 *
 * Está diseñada para ser utilizada en sistemas de tokenización basados en autómatas,
 * donde cada transición válida produce una instancia consistente de token.
 *
 * @package DLParse\Core\Config\Parser
 * @version v0.0.1 (release)
 * @author David E Luna M
 * @license MIT
 *
 * @property-read string $content Contenido textual exacto extraído del flujo de entrada.
 * @property-read string $type Tipo sintáctico del token (clasificación léxica).
 * @property-read int $line Número de línea donde se emitió el token.
 * @property-read int $column Número de columna donde se emitió el token.
 * @property-read int $offset Posición absoluta del cursor en el flujo de entrada.
 * @property-read int $length Tamaño del contenido del token
 */
final class Lexeme {
    /**
     * Contenido textual exacto del token tal como fue reconocido por el analizador léxico.
     *
     * @var string
     */
    public readonly string $content;

    /**
     * Tipo sintáctico del token, definido por el conjunto de reglas léxicas.
     *
     * @var string
     */
    public readonly string $type;

    /**
     * Número de línea en el flujo de entrada donde se emitió el token.
     *
     * @var int
     */
    public readonly int $line;

    /**
     * Número de columna en la línea donde se emitió el token.
     *
     * @var int
     */
    public readonly int $column;

    /**
     * Posición absoluta (offset) del cursor dentro del flujo de entrada al momento de emitir el token.
     *
     * @var int
     */
    public readonly int $offset;

    /**
     * Tamaño en bytes del token
     *
     * @var integer
     */
    public readonly int $length;

    /**
     * Establece el lexema del token.
     *
     * Este valor corresponde a la secuencia exacta de caracteres reconocida por el lexer.
     *
     * @param string $lexeme Fragmento del flujo de entrada asociado al token.
     * @return self
     */
    public function set_content(string $lexeme): self {
        $this->content = $lexeme;
        return $this;
    }

    /**
     * Define el tipo sintáctico del token.
     *
     * Este valor es utilizado por el parser para determinar la producción gramatical aplicable.
     *
     * @param string $type Identificador del tipo de token.
     * @return self
     */
    public function set_type(string $type): self {
        $this->type = $type;
        return $this;
    }

    /**
     * Establece el número de línea donde se generó el token.
     *
     * @param int $line Línea dentro del flujo de entrada.
     * @return self
     */
    public function set_line(int $line): self {
        $this->line = $line;
        return $this;
    }

    /**
     * Establece el número de columna donde se generó el token.
     *
     * @param int $column Columna dentro de la línea correspondiente.
     * @return self
     */
    public function set_column(int $column): self {
        $this->column = $column;
        return $this;
    }

    /**
     * Establece el desplazamiento absoluto del cursor en el flujo de entrada.
     *
     * Este valor permite correlacionar el token con su posición exacta en memoria o buffer.
     *
     * @param int $offset Posición absoluta del cursor.
     * @return self
     */
    public function set_offset(int $offset): self {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Verifica que todas las propiedades se hayan cargado correctamente
     *
     * @return void
     */
    public function assert_complete(): void {
        /** @var bool $completed */
        $completed = isset($this->content, $this->type, $this->line, $this->column, $this->offset);

        if (!$completed) {
            throw new TokenizerException("Lexeme incompleto antes de sellar.");
        }
    }
}