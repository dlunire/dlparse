<?php

declare(strict_types=1);

namespace DLParse\Core\Config\Parser;

/**
 * Copyright (c) 2025 David E Luna M
 * Licensed under the MIT License. See LICENSE file for details.
 */

/**
 * Representa el conjunto de tipos de tokens reconocidos por el analizador léxico.
 *
 * Este enum define el alfabeto de salida (Σₜ) del proceso de tokenización,
 * donde cada valor corresponde a una categoría semántica derivada de la
 * secuencia de entrada procesada por el autómata.
 *
 * En el contexto de un autómata léxico (scanner), cada instancia de TokenType
 * es el resultado de una o más transiciones válidas sobre la cinta de entrada,
 * agrupando símbolos en unidades significativas (tokens).
 *
 * Formalmente:
 * - Σ  → alfabeto de entrada (caracteres)
 * - Σₜ → alfabeto de salida (tipos de token)
 *
 * Este enum actúa como dominio de clasificación para los tokens generados,
 * permitiendo desacoplar la fase léxica de la sintáctica.
 *
 * Consideraciones de diseño:
 * - El uso de `int` como tipo base permite optimización en comparaciones
 *   y almacenamiento.
 * - Cada caso debe ser único y representar una categoría léxica bien definida.
 *
 * @package DLParse\Core\Config\Parser
 * @version v0.0.1
 * @author David E Luna M
 */
enum TokenType: int {

    /**
     * Representa un identificador léxico válido.
     *
     * Corresponde a una secuencia de símbolos que cumple con la
     * gramática definida para nombres (variables o claves).
     *
     * Formalmente:
     * IDENTIFIER ∈ Σ⁺ sujeto a reglas de formación (autómata).
     *
     * @var int
     */
    case IDENTIFIER = 1;

    /**
     * Delimitador de anotación de tipo.
     *
     * Símbolo literal `:` que separa un identificador de su tipo declarado,
     * formando una construcción del tipo:
     *
     * IDENTIFIER : type
     *
     * Este token no representa una asignación, sino una relación de tipado
     * dentro de la gramática del lenguaje.
     *
     * @var int
     */
    case COLUMN = 2;

    /**
     * Operador de asignación.
     *
     * Símbolo literal `=` que establece la relación entre una declaración
     * tipada y su valor asociado.
     *
     * Forma parte de una construcción completa del tipo:
     *
     * IDENTIFIER: type = value
     *
     * Este token marca la transición desde la fase de declaración
     * (identificador y tipo) hacia la fase de inicialización (valor)
     * dentro del flujo del autómata.
     *
     * @var int
     */
    case ASSIGN = 3;

    /**
     * Representa el valor asociado a un identificador.
     *
     * Es el resultado de consumir una secuencia de símbolos posterior
     * al operador de asignación, hasta encontrar un delimitador válido
     * o el final de la entrada.
     *
     * Nota:
     * > Puede requerir subclasificación futura (string, numérico, booleano, etc.).
     *
     * @var int
     */
    case VALUE = 5;

    /**
     * Comentario de una sola línea iniciado por `#`.
     *
     * Consume todos los símbolos desde `#` hasta un salto de línea
     * (LF o CRLF), sin afectar el flujo semántico del análisis.
     *
     * @var int 
     */
    case HASH_LINE_COMMENT = 20;

    /**
     * Comentario de una sola línea iniciado por `//`.
     *
     * Léxicamente equivalente a HASH_LINE_COMMENT, pero diferenciado
     * por su símbolo inicial, lo cual permite análisis posteriores
     * más precisos o compatibilidad multi-sintaxis.
     *
     * @var int
     */
    case LINE_COMMENT = 21;

    /**
     * Comentario de bloque delimitado.
     *
     * Representa una secuencia de símbolos comprendida entre
     * delimitadores de apertura y cierre.
     *
     * Puede abarcar múltiples líneas y requiere control explícito
     * de estados para detectar correctamente su finalización.
     *
     * @var int
     */
    case BLOCK_COMMENT = 22;
}