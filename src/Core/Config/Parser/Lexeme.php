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
     * Indica si la instancia ha sido sellada (inmutabilizada).
     *
     * Una vez establecido en `true`, el token se considera completamente
     * construido y no permite modificaciones adicionales en sus propiedades.
     *
     * Este mecanismo define explícitamente la transición de un estado mutable
     * (fase de construcción durante la tokenización) a un estado inmutable
     * (token emitido listo para consumo por el parser).
     *
     * @var bool
     */
    private bool $sealed = false;

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
     * Asegura que la unidad léxica no haya sido sellada antes de una modificación.
     *
     * Este guardián de integridad verifica que la instancia se encuentre aún en su
     * fase mutable de construcción. Si el lexema ya ha sido sellado (`sealed`), 
     * se prohíbe cualquier cambio posterior para garantizar la inmutabilidad de 
     * los metadatos (lexema, tipo y posición) durante el análisis sintáctico.
     *
     * @throws TokenizerException Si se intenta modificar una propiedad de un 
     *                            lexema que ya ha sido marcado como inmutable.
     * @return void
     */
    private function assert_not_sealed(): void {
        if ($this->sealed) {
            throw new TokenizerException(
                "Error de integridad: No se puede modificar un Lexeme que ya ha sido sellado. La instancia es inmutable una vez emitida para su procesamiento en el Parser."
            );
        }
    }


    /**
     * Establece el lexema del token.
     *
     * Este valor corresponde a la secuencia exacta de caracteres reconocida por el lexer.
     *
     * @param string $lexeme Fragmento del flujo de entrada asociado al token.
     * @return self
     */
    public function set_lexeme(string $lexeme): self {
        $this->assert_not_sealed();
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
        $this->assert_not_sealed();
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
        $this->assert_not_sealed();
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
        $this->assert_not_sealed();
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
        $this->assert_not_sealed();
        $this->offset = $offset;
        return $this;
    }

    /**
     * Sella la instancia actual del lexema para garantizar su inmutabilidad.
     *
     * Al invocar este método, el lexema transita de un estado mutable (fase de construcción
     * en el Lexer) a un estado inmutable (listo para consumo por el Parser). Una vez sellado,
     * la lógica de integridad impedirá cualquier modificación posterior, asegurando que 
     * los metadatos de posición y valor permanezcan consistentes durante todo el análisis.
     *
     * @return self Retorna la instancia actual sellada para encadenamiento de métodos (fluent interface).
     */
    public function seal(): self {
        $this->sealed = true;
        $this->length = \strlen($this->content);
        return $this;
    }
}