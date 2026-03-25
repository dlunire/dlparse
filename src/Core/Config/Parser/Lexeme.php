<?php

declare(strict_types=1);

namespace DLParse\Core\Config\Parser;

use DLParse\Core\Config\Parser\Enums\TokenType;
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
 * @property-read string $lexeme_content Contenido textual exacto extraído del flujo de entrada.
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
    public readonly string $lexeme_content;

    /**
     * Tipo sintáctico del token, definido por el conjunto de reglas léxicas.
     *
     * @var string
     */
    public readonly TokenType $type;

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
     * Constructor con múltiples parámetros justificado por performance crítica
     * durante tokenización. La emisión masiva de tokens requiere minimizar
     * overhead de llamadas a funciones.
     * 
     * Construye una instancia de Token con metadatos léxicos.
     *
     * Este constructor inicializa un token capturado durante el análisis léxico,
     * asociando el contenido léxico (lexema) con su clasificación semántica
     * y su posición exacta en la cinta de entrada.
     *
     * Los parámetros de posición (línea, columna, offset, length) son esenciales para:
     * - Reportes de error precisos (errores sintácticos o semánticos)
     * - Trazabilidad del token en el código fuente original
     * - Reconstrucción del span léxico original (offset → offset + length)
     * - Debugging y análisis de flujo léxico
     *
     * @param string $lexeme_content     El contenido literal del token tal como
     *                                   aparece en la cinta de entrada. Representa
     *                                   la secuencia de caracteres que activó la
     *                                   transición del autómata léxico.
     *
     * @param TokenType $tokentype       Clasificación semántica del token dentro
     *                                   del alfabeto de salida (Σₜ). Determina
     *                                   la categoría léxica a la cual pertenece
     *                                   este lexema.
     *
     * @param int $line                  Número de línea (basado en 1) donde comienza
     *                                   el token en la fuente original. Usado para
     *                                   mensajes de error y mapeo de posiciones.
     *
     * @param int $column                Número de columna (basado en 1) donde comienza
     *                                   el token dentro de su línea. Complementa $line
     *                                   para ubicación bidimensional exacta.
     *
     * @param int $offset                Posición absoluta en bytes desde el inicio
     *                                   de la cinta de entrada. Marca el primer byte
     *                                   del lexema y permite búsquedas y saltos directos.
     *
     * @param int $length                Tamaño en bytes del lexema. Junto con $offset,
     *                                   define el span completo: [offset, offset + length).
     *                                   Esencial para multi-byte encodings (UTF-8) donde
     *                                   strlen($lexeme_content) puede diferir del tamaño
     *                                   en bytes de la secuencia original.
     *
     * @return self                      Retorna la instancia del Token inicializado.
     */
    public function __construct(string $lexeme_content, TokenType $tokentype, int $line, int $column, int $offset, int $length) {
        $this->lexeme_content = $lexeme_content;
        $this->type = $tokentype;
        $this->line = $line;
        $this->column = $column;
        $this->offset = $offset;
        $this->length = $length;
    }

    /**
     * Verifica que todas las propiedades se hayan cargado correctamente
     *
     * @return self
     */
    public function assert_complete(): self {
        /** @var bool $completed */
        $completed = isset($this->lexeme_content, $this->type, $this->line, $this->column, $this->offset);

        if (!$completed) {
            throw new TokenizerException("Lexeme incompleto antes de sellar.");
        }

        return $this;
    }
}