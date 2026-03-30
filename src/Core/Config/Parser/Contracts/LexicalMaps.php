<?php

declare(strict_types=1);

namespace DLParse\Core\Config\Parser\Contracts;

/**
 * Interfaz que define los mapas léxicos para análisis y parsing.
 *
 * Esta interfaz proporciona constantes protegidas que actúan como mapas de búsqueda rápida
 * (lookup tables) para validar y clasificar caracteres durante el proceso de análisis léxico
 * de un parser. Los mapas están optimizados para búsquedas en tiempo O(1).
 *
 * @package DLParse\Core\Config\Parser\Contracts
 * @since 1.0.0
 * @author David E Luna M / DLUnire
 * @copyright (c) 2026 David E Luna M / DLUnire
 */
interface LexicalMaps {

    /**
     * Mapa de caracteres permitidos en identificadores.
     *
     * Define el conjunto de caracteres válidos que pueden aparecer en un identificador.
     * Incluye las 26 letras mayúsculas del alfabeto latino (A-Z) y el guion bajo (_),
     * comúnmente utilizado como separador o como carácter válido en nombres de variables.
     *
     * Los identificadores son secuencias de caracteres que representan nombres de variables,
     * funciones, constantes, clases, etc. Este mapa se utiliza para validar que cada
     * carácter pertenece al conjunto permitido durante el análisis léxico.
     *
     * @const IDENTIFIER_MAP
     * @var non-empty-array<non-empty-string, true>
     * 
     * @example Ejemplo de uso
     * ```
     * // Uso para validar caracteres de identificador
     * $char = 'A';
     * if (LexicalMaps::IDENTIFIER_MAP[$char] ?? false) {
     *     // El carácter es válido en un identificador
     * }
     * ```
     *
     * @note Este mapa contiene solo mayúsculas. Para un identificador completo (case-insensitive),
     *       considera convertir los caracteres a mayúsculas antes de validar.
     *
     * @see LexicalMaps::TYPE_MAP
     */
    public const IDENTIFIER_MAP = [
        "\x41" => true, "\x42" => true, "\x43" => true, "\x44" => true, "\x45" => true,
        "\x46" => true, "\x47" => true, "\x48" => true, "\x49" => true, "\x4a" => true,
        "\x4b" => true, "\x4c" => true, "\x4d" => true, "\x4e" => true, "\x4f" => true,
        "\x50" => true, "\x51" => true, "\x52" => true, "\x53" => true, "\x54" => true,
        "\x55" => true, "\x56" => true, "\x57" => true, "\x58" => true, "\x59" => true,
        "\x5a" => true, "\x5f" => true
    ];

    /**
     * Mapa de caracteres permitidos en anotaciones de tipo.
     *
     * Define el conjunto de caracteres válidos que pueden aparecer en anotaciones de tipo.
     * Incluye las 26 letras minúsculas del alfabeto latino (a-z).
     *
     * Las anotaciones de tipo son cadenas que especifican el tipo de dato que se espera,
     * como 'string', 'int', 'bool', 'mixed', o nombres de clases personalizadas.
     * Este mapa se utiliza para validar cada carácter durante el análisis de estas anotaciones.
     *
     * @const TYPE_MAP
     * @var non-empty-array<non-empty-string, true>
     *
     * @example
     * // Uso para validar caracteres de anotación de tipo
     * $char = 'a';
     * if (isset(LexicalMaps::TYPE_MAP[$char])) {
     *     // El carácter es válido en una anotación de tipo
     * }
     *
     * @note Este mapa contiene solo minúsculas. Para validar tipos completos (case-insensitive),
     *       considera convertir los caracteres a minúsculas antes de validar.
     *
     * @see LexicalMaps::IDENTIFIER_MAP
     */
    public const TYPE_MAP = [
        "\x61" => true, "\x62" => true, "\x63" => true, "\x64" => true, "\x65" => true,
        "\x66" => true, "\x67" => true, "\x68" => true, "\x69" => true, "\x6a" => true,
        "\x6b" => true, "\x6c" => true, "\x6d" => true, "\x6e" => true, "\x6f" => true,
        "\x70" => true, "\x71" => true, "\x72" => true, "\x73" => true, "\x74" => true,
        "\x75" => true, "\x76" => true, "\x77" => true, "\x78" => true, "\x79" => true,
        "\x7a" => true
    ];

    /**
     * Delimitador de anotación de tipo
     * 
     * @var non-empty-string
     */
    public const COLON = "\x3a";

    /**
     * Operaor de asignación
     * 
     * @var non-empty-string
     */
    public const ASSIGN = "\x3d";

    /**
     * Cadena de texto con comillas dobles
     * 
     * @var non-empty-string
     */
    public const STRING_DOUBLE_QUOTES = "\x22";

    /**
     * Cadena de texto con comillas simples
     * 
     * @var non-empty-string
     */
    public const STRING_SIMPLE_QUOTES = "\x27";

    /**
     * Cadena de texto con comillas invertidas (Backticks)
     * 
     * @var non-empty-string
     */
    public const STRING_BACKTICK_QUOTES = "\x60";

    /**
     * Delimitador de inicio para bloques de texto heredados (Heredoc)
     * 
     * @var non-empty-string
     */
    public const STRING_HEREDOC_START = "\x3c\x3c\x3c";

    /**
     * Delimitador de cierre para bloques de contenido (Heredoc invertido)
     * 
     * @var non-empty-string
     */
    public const STRING_HEREDOC_END = "\x3e\x3e\x3e";

    /**
     * Comentario con apertura `#`
     * 
     * @var non-empty-string
     */
    public const HASH_LINE_COMMENT = "\x23";

    /**
     * Símbolo de inicio para comentarios de línea o de bloque.
     *
     * Representa el carácter `/`, que actúa como punto de bifurcación en el
     * autómata léxico para la detección de construcciones de comentario.
     *
     * A partir de este símbolo, el sistema puede transitar hacia:
     * - Comentario de línea (`//`)
     * - Comentario de bloque (`\x2f\x2a ... \x2a\x2f`)
     * - Otros operadores válidos del lenguaje (dependiendo de la gramática)
     *
     * Este valor no es un comentario por sí mismo, sino un prefijo ambiguo
     * que requiere inspección del símbolo siguiente (lookahead) para resolver
     * la transición correcta del autómata.
     *
     * @var non-empty-string
     */
    public const SLASH_MARKER = "\x2f";

    /**
     * Carácter de escape: Barra invertida (Backslash) `\`.
     * * Se utiliza dentro del autómata para anular el significado especial del 
     * siguiente carácter (secuencias de escape), permitiendo la inclusión de 
     * delimitadores o caracteres de control dentro de lexemas de tipo string 
     * o comentarios.
     * * Valor ASCII: 92 (0x5c).
     */
    public const BACK_SLASH = "\x5c";

    /**
     * Carácter asterisco (*) utilizado como terminador de comentarios de bloque.
     *
     * Representa el símbolo `*` (asterisco), que desempeña un papel crucial en el
     * análisis léxico para identificar el cierre de comentarios de bloque.
     * Forma parte de la secuencia de terminación  que delimita el final de
     * un bloque de comentarios iniciado con `/*`.
     *
     * Aunque el asterisco puede utilizarse también como operador de multiplicación
     * en expresiones aritméticas, en el contexto de este parser léxico se utiliza
     * principalmente para resolver la transición de cierre en comentarios de bloque.
     *
     * Este carácter se identifica mediante su valor hexadecimal `\x2a` (42 en decimal)
     * y es fundamental para la correcta tokenización de construcciones multi-línea
     * de comentarios.
     *
     * @const ASTERISK
     * @var non-empty-string Representación del carácter asterisco en forma hexadecimal
     *
     * @example
     * // Transición de cierre en comentario de bloque
     * if ($char === LexicalMaps::ASTERISK && $nextChar === LexicalMaps::SLASH_MARKER) {
     *     // Se encontró, terminar el comentario de bloque
     * }
     *
     * @see LexicalMaps::SLASH_MARKER Para el inicio de comentarios (`/`) y cierre
     * @since 1.0.0
     */
    public const ASTERISK = "\x2a";

    public const BLOCK_COMMENT_END = "\x2a\x2f";

    /**
     * Mapa para validación de caracteres Hexadecimales (0-9, a-f, A-F).
     * Crucial para UUIDs y valores de color/bytes.
     * 
     * @var non-empty-array<non-empmty-string, true>
     */
    public const HEX_MAP = [
        // Números 0-9 (\x30 - \x39)
        "\x30" => true, "\x31" => true, "\x32" => true, "\x33" => true, "\x34" => true,
        "\x35" => true, "\x36" => true, "\x37" => true, "\x38" => true, "\x39" => true,
        // Letras A-F (\x41 - \x46)
        "\x41" => true, "\x42" => true, "\x43" => true, "\x44" => true, "\x45" => true, "\x46" => true,
        // Letras a-f (\x61 - \x66)
        "\x61" => true, "\x62" => true, "\x63" => true, "\x64" => true, "\x65" => true, "\x66" => true
    ];

    /**
     * Retorno de carro del sistema para los casos de uso de Windows como sistema operativo
     * 
     * @var non-empty-string
     */
    public const CR = "\x0d";
}