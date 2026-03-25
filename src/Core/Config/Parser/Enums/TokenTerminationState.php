<?php

/**
 * Copyright (c) 2026 David E Luna M
 * Licensed under the MIT License. See LICENSE file for details.
 */

declare(strict_types=1);

namespace DLParse\Core\Config\Parser\Enums;

/**
 * Define los bytes de finalización esperados para cada clase de token.
 *
 * Este enum mapea condiciones de término del lexema actual con sus bytes
 * terminadores correspondientes. Permite validación explícita: cuando el
 * scanner emite un token, "sabe" qué byte debe encontrar para confirmar
 * que la transición al siguiente lexema es válida.
 *
 * Cada caso contiene el valor byte esperado (backing value = string).
 * Esto permite comparación directa:
 *
 * ```php
 * if ($this->token_termination_state->value === $byte) {
 *     // Finalización confirmada
 * }
 * ```
 *
 * Relación con ScannerAction:
 * - ScannerAction define qué hacer CON el byte actual
 * - TokenTerminationState define qué byte debe venir DESPUÉS de EMIT
 *
 * @package DLParse\Core\Config\Parser
 * @version v0.0.1
 * @author David E Luna M
 * @license MIT
 */
enum TokenTerminationState: string {

    /**
     * Sin requisito de finalización.
     *
     * El token puede ser seguido por cualquier byte válido.
     * No hay condición especial post-emisión.
     *
     * Casos de uso:
     * - Tokens finales en el flujo (EOF implícito)
     * - Transiciones donde el siguiente byte se valida de otro modo
     *
     * @var empty-string
     */
    case NONE = '';

    /**
     * Finalización por salto de línea (LF).
     *
     * El token debe cerrarse cuando se detecte un carácter de fin de línea.
     * Se utiliza LF (\x0A) como byte de referencia porque el autómata
     * procesa bytes individuales.
     *
     * Aplicable para:
     * - Comentarios de línea (`//` o `#`)
     * - Token VALUE (el valor termina en fin de línea)
     *
     * Nota:
     * Si la entrada usa CRLF (\x0D\x0A), el CR se trata como parte del token.
     * La normalización en pre-procesamiento debe homogeneizar a LF.
     *
     * @var non-empty-string
     */
    case LINE_TERMINATOR = "\x0A";

    /**
     * Finalización de comentario de bloque: cierre (`\x2a\x2f`).
     *
     * Este caso es especial: requiere validación de DOS bytes.
     * El scanner espera un asterisco (`\x2a`), y cuando lo detecta,
     * hace lookahead del siguiente byte para confirmar slash (`\x2f`).
     *
     * Valor almacenado: `\x2f` (el slash del cierre `\x2a\2f`)
     *
     * Comportamiento:
     * - Cuando se detecta `*` → PROBE (validación tentativa)
     * - Si `*` + `/` confirmado → emite BLOCK_COMMENT
     * - Si no se confirma → continúa como contenido del comentario
     *
     * @var non-empty-string
     */
    case BLOCK_TERMINATOR = "\x2f";

    /**
     * Finalización de IDENTIFIER: delimitador de separación.
     *
     * El token IDENTIFIER termina cuando se encuentra un colon (`:`),
     * que actúa como delimitador estructural separando la variable
     * de su anotación de tipo.
     *
     * Estructura: `IDENTIFIER : TYPE = VALUE`
     *             ^          ^ este byte finaliza IDENTIFIER
     *
     * @var non-empty-string
     */
    case IDENTIFIER_TERMINATOR = "\x3a";

    /**
     * Finalización de TYPE: operador de asignación.
     *
     * El token TYPE termina cuando se encuentra un signo igual (`=`),
     * que actúa como delimitador estructural separando la anotación
     * de tipo del valor asignado.
     *
     * Estructura: `IDENTIFIER : TYPE = VALUE`
     *                          ^    ^ este byte finaliza TYPE
     *
     * @var non-empty-string
     */
    case TYPE_TERMINATOR = "\x3d";

    /**
     * Finalización de string con comillas dobles.
     *
     * El token VALUE termina cuando se detecta una comilla doble (`\x22`)
     * sin escape previo. El contenido entre comillas puede contener
     * cualquier byte arbitrario (UTF-8, caracteres especiales, caracteres
     * de control, etc.), siempre que se respeten las secuencias de escape
     * estándar.
     *
     * Byte esperado: \x22 (")
     * Modo de consumo: CONSUME_ESCAPED
     * Byte de escape: \x5c (\)
     *
     * Estructura sintáctica:
     * ```
     * TYPE = "contenido arbitrario con escapes"
     *        ^ inicio del string      ^ final (cierre sin escape)
     * ```
     *
     * Comportamiento:
     * - Al detectar `"` → entra en modo CONSUME_ESCAPED
     * - Acumula TODO byte por byte sin restricción léxica
     * - Si detecta `\` (escape) → consume también el siguiente byte sin validar
     * - Si detecta `"` sin escape previo → emite el token
     * - Las comillas escapadas `\"` se tratan como contenido literal
     *
     * Casos de uso:
     * - Strings normales: `"hola mundo"`
     * - Strings con comillas escapadas: `"texto con \"comillas\""`
     * - Strings con barras: `"rutas\\como\\esta"`
     * - Strings con caracteres especiales: `"datos: áéíóú !@#$%^&*()"`
     *
     * Ejemplo:
     * ```none
     * MENSAJE: string = "Hola \"Mundo\" con \\ barra"
     *                   ^                              ^
     *                   inicio                         final
     *
     * Contenido emitido: Hola \"Mundo\" con \\ barra
     * (sin incluir las comillas delimitadoras)
     * ```
     *
     */
    case DOUBLE_QUOTES_TERMINATOR = "\x22";

    /**
     * Finalización de string con comillas simples.
     *
     * El token VALUE termina cuando se detecta una comilla simple (`\x27`)
     * sin escape previo. Similar a DOUBLE_QUOTES_TERMINATOR pero con
     * un delimitador diferente.
     *
     * Byte esperado: \x27 (')
     * Modo de consumo: CONSUME_ESCAPED
     * Byte de escape: \x5c (\)
     *
     * Estructura sintáctica:
     * ```
     * TYPE = 'contenido arbitrario con escapes'
     *        ^ inicio del string      ^ final (cierre sin escape)
     * ```
     *
     * Comportamiento:
     * - Al detectar `'` → entra en modo CONSUME_ESCAPED
     * - Acumula TODO byte por byte sin restricción léxica
     * - Si detecta `\` (escape) → consume también el siguiente byte sin validar
     * - Si detecta `'` sin escape previo → emite el token
     * - Las comillas escapadas `\'` se tratan como contenido literal
     *
     * Diferencia con DOUBLE_QUOTES_TERMINATOR:
     * - Solo cambia el delimitador (`'` vs `"`)
     * - El procesamiento de escape es idéntico
     *
     * Casos de uso:
     * - Strings simples: `'hola mundo'`
     * - Strings con comillas escapadas: `'texto con \'comillas\''`
     * - Strings con barras: `'rutas\\como\\esta'`
     * - Strings con caracteres especiales: `'datos: áéíóú !@#$%^&*()'`
     *
     * Ejemplo:
     * ```none
     * RUTA: string = 'C:\\Users\\archivo.txt'
     *                ^                       ^
     *                inicio                  final
     *
     * Contenido emitido: C:\\Users\\archivo.txt
     * (sin incluir las comillas delimitadoras)
     * ```
     *
     */
    case SIMPLE_QUOTES_TERMINATOR = "\x27";

    /**
     * Finalización de string con backticks.
     *
     * El token VALUE termina cuando se detecta un backtick (`` \x60 ``)
     * sin escape previo. Utilizado en algunos lenguajes para templating,
     * comandos de shell o strings especiales.
     *
     * Byte esperado: \x60 (`)
     * Modo de consumo: CONSUME_ESCAPED
     * Byte de escape: \x5c (\)
     *
     * Estructura sintáctica:
     * ```
     * TYPE = `contenido arbitrario con escapes`
     *        ^ inicio del string      ^ final (cierre sin escape)
     * ```
     *
     * Comportamiento:
     * - Al detectar `` ` `` → entra en modo CONSUME_ESCAPED
     * - Acumula TODO byte por byte sin restricción léxica
     * - Si detecta `\` (escape) → consume también el siguiente byte sin validar
     * - Si detecta `` ` `` sin escape previo → emite el token
     * - Los backticks escapados `` \` `` se tratan como contenido literal
     *
     * Diferencia con DOUBLE_QUOTES_TERMINATOR y SIMPLE_QUOTES_TERMINATOR:
     * - Solo cambia el delimitador (`` ` `` vs `"` vs `'`)
     * - El procesamiento de escape es idéntico
     *
     * Casos de uso:
     * - Template strings: `` `Hola ${nombre}` ``
     * - Comandos shell: `` `ls -la` ``
     * - Strings de SQL: `` `SELECT * FROM tabla` ``
     * - Strings crudos con backticks literales: `` `texto con \`backticks\`` ``
     *
     * Ejemplo:
     * ```
     * COMANDO: string = `echo "Hola Mundo" > archivo.txt`
     *                   ^                               ^
     *                   inicio                          final
     *
     * Contenido emitido: echo "Hola Mundo" > archivo.txt
     * (sin incluir los backticks delimitadores)
     * ```
     *
     * Notas:
     * - El contenido puede incluir comillas dobles y simples sin escape
     * - El contenido puede incluir caracteres de control
     * - Ideal para contenido que contiene comillas sin necesidad de escape
     *
     */
    case STRING_BACKTICK = "\x60";

    /**
     * Finalización de heredoc: secuencia terminal multi-byte.
     *
     * El token VALUE termina cuando se detecta la secuencia de cierre
     * heredoc: `>>>` (\x3e\x3e\x3e) seguida inmediatamente del identificador
     * original que se definió en la apertura.
     *
     * Secuencia esperada: \x3e\x3e\x3e (>>>) + identificador dinámico
     * Modo de consumo: CONSUME_UNTIL_SEQUENCE
     * Escape: NO (heredoc no procesa escapes)
     * Identificador: dinámico, capturado en `$current_heredoc_identifier`
     * Secuencia completa: almacenada en `$termination_sequence`
     *
     * Estructura sintáctica:
     * ```
     * VARIABLE: TYPE = <<<IDENTIFICADOR
     * [contenido arbitrario sin restricciones]
     * >>>IDENTIFICADOR
     * ```
     *
     * Desglose de componentes:
     * - `<<<` → Inicializador heredoc (apertura)
     * - `IDENTIFICADOR` → Marca de apertura (dinámico, ej: "EOF", "SQL", "DATA")
     * - `\n` → Salto de línea obligatorio después del identificador
     * - `[contenido]` → Cuerpo multilínea (TODO sin restricción)
     * - `>>>` → Secuencia de cierre (3 bytes: \x3e\x3e\x3e)
     * - `IDENTIFICADOR` → Marca de cierre (DEBE coincidir exactamente con la apertura)
     *
     * Comportamiento:
     * 1. Al detectar `<<<` → captura identificador hasta fin de línea
     * 2. Entra en modo CONSUME_UNTIL_SEQUENCE
     * 3. Construye secuencia terminal: ">>>" + identificador (ej: ">>>EOF")
     * 4. Acumula TODO byte por byte sin validación léxica, incluidos:
     *    - Saltos de línea arbitrarios
     *    - Caracteres de control
     *    - UTF-8 multibyte
     *    - Caracteres especiales (sin procesamiento de escape)
     * 5. Cuando detecta `>` → verifica si coincide con la secuencia completa
     * 6. Si coincide → emite el token (sin incluir ">>>IDENTIFICADOR")
     * 7. Salta la secuencia de cierre
     *
     * Casos de uso:
     * - Bloques de texto multilínea: <<<EOF ... >>>EOF
     * - Bloques SQL: <<<SQL ... >>>SQL
     * - Bloques JSON: <<<JSON ... >>>JSON
     * - Bloques HTML: <<<HTML ... >>>HTML
     * - Datos crudos (raw): <<<RAW ... >>>RAW
     * - Nowdocs (variante sin interpretación): <<<'IDENTIFICADOR' ... >>>IDENTIFICADOR
     *
     * Ejemplo completo:
     * ```
     * CONSULTA: string = <<<SQL
     * SELECT id, nombre, correo
     * FROM usuarios
     * WHERE activo = 1
     * ORDER BY fecha_creacion DESC
     * >>>SQL
     * 
     * Parsing:
     * - Detecta <<<SQL → captura identificador "SQL"
     * - termination_sequence = ">>>SQL"
     * - Acumula TODO hasta detectar ">>>SQL"
     * - Emite token con contenido:
     *   SELECT id, nombre, correo
     *   FROM usuarios
     *   WHERE activo = 1
     *   ORDER BY fecha_creacion DESC
     *   (sin incluir >>>SQL)
     * ```
     *
     * Notas importantes:
     * - El contenido puede incluir cualquier byte sin restricción
     * - NO se procesan escapes (a diferencia de strings con comillas)
     * - La búsqueda de secuencia terminal es byte-sensible
     * - El identificador de cierre DEBE coincidir exactamente con el de apertura
     * - La secuencia `>>>` es literal (no como comparación de `>`+`>`+`>`)
     *
     */
    case STRING_HEREDOC_TERMINATOR = "\x3e\x3e\x3e";
}