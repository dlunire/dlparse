<?php

/**
 * Copyright (c) 2026 David E Luna M
 * Licensed under the MIT License. See LICENSE file for details.
 */

declare(strict_types=1);

namespace DLParse\Core\Config\Parser;

/**
 * Representa las acciones de control del scanner durante la tokenización.
 *
 * Este enum modela el comportamiento operativo del scanner respecto al consumo
 * de bytes en cada iteración. No representa estados léxicos del lenguaje en sí
 * (IDENTIFIER, TYPE, VALUE), sino instrucciones sobre **cómo procesar el byte actual**.
 *
 * Relación dual con bytes:
 * - Bytes en la cinta = transiciones del DFA (q₀ → q₁ → ... → qₙ)
 * - ScannerAction = función de acción γ(q, a): qué hacer CON ese byte
 *
 * En cada iteración del scanner:
 * 1. El byte actual YA ha sido leído (y conceptualmente consumido)
 * 2. Se evalúa la transición del DFA → determina ScannerAction
 * 3. Se ejecuta la acción correspondiente
 * 4. El puntero AVANZA (el lookahead usa peek, no consume)
 *
 * Notas:
 * - Las acciones EXPECT y PROBE utilizan lookahead mediante inspección (peek)
 *   del siguiente byte, sin alterar el puntero.
 * - Este modelo implementa un transductor léxico determinista con validación
 *   estructural explícita.
 *
 * @package DLParse\Core\Config\Parser
 * @version v0.0.1
 * @author David E Luna M
 * @license MIT
 */
enum ScannerAction: int {

    /**
     * Saltar: excluir el byte actual del lexema en construcción.
     *
     * El byte es procesado pero NO forma parte del rango del lexema actual.
     * El scanner permanece en el mismo estado léxico.
     *
     * Casos de uso:
     * - Bytes que no formen parte del lexema.
     *
     * @var int
     */
    case SKIP = 0;

    /**
     * Expect: validación estricta de secuencia multi-byte.
     *
     * El byte actual inicia una secuencia cuya continuación es OBLIGATORIA.
     * El scanner realiza un lookahead (peek) del siguiente byte.
     *
     * Comportamiento:
     * - Si el siguiente byte coincide con lo esperado → transición válida
     * - Si NO coincide → error léxico inmediato (fail-fast)
     *
     * El byte actual no se acumula aún en el lexema.
     *
     * Casos de uso:
     * - Detectado `\x2f` → se espera `\x2f` (comentario de línea) o `\x2a` (comentario de bloque)
     *   Cualquier otro byte → error
     *
     * Semántica:
     * EXPECT = "la continuación debe cumplir una condición, o el input es inválido"
     *
     * @var int
     */
    case EXPECT = 1;

    /**
     * Append: extender el rango del lexema actual.
     *
     * El byte actual pasa a formar parte del lexema en construcción,
     * extendiendo el cursor (rango [inicio, fin]) sobre la cinta de entrada.
     *
     * El scanner continúa en el mismo estado léxico.
     *
     * Casos de uso:
     * - Construcción de IDENTIFIER.
     * - Acumulación en TYPE o VALUE.
     * - Contenido dentro de comentarios de bloque.
     *
     * @var int
     */
    case APPEND = 2;

    /**
     * Probe: validación tentativa de secuencia multi-byte.
     *
     * El byte actual puede iniciar una secuencia especial, pero su continuación
     * NO es obligatoria. El scanner realiza un lookahead (peek) sin lanzar error.
     *
     * Comportamiento:
     * - Si el siguiente byte coincide → se confirma la secuencia (ej. cierre)
     * - Si NO coincide → el byte se trata como parte normal del flujo
     *
     * El byte actual no se acumula inmediatamente; la acción posterior depende
     * del resultado del lookahead.
     *
     * Casos de uso:
     * - Detectado `*` dentro de comentario de bloque → posible cierre
     *   Si NO se confirma → se trata como contenido del comentario
     *
     * Semántica:
     * PROBE = "intenta validar una continuación, pero no es obligatoria"
     *
     * @var int
     */
    case PROBE = 3;

    /**
     * Emit: finalizar y emitir el lexema actual como token.
     *
     * El byte actual actúa como delimitador o terminador del lexema en curso.
     *
     * Comportamiento:
     * 1. Se finaliza el lexema acumulado (SIN incluir el byte actual)
     * 2. Se emite el token correspondiente
     * 3. Se reinicia el estado para el siguiente lexema
     * 4. El byte actual puede ser reprocesado o descartado según el contexto
     *
     * Casos de uso:
     * - `:` → cierra IDENTIFIER, emite COLON
     * - `=` → cierra TYPE, emite ASSIGN
     * - `\n` → cierra VALUE
     * - EOF → cierra lexema pendiente
     *
     * @var int
     */
    case EMIT = 4;
}