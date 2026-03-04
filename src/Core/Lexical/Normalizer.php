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

namespace DLParse\Core\Lexical;

use DLParse\Exceptions\NormalizerException;
use DLParse\Exceptions\TokenizerException;

/**
 * Normalizador de bytes, previo a la tokenización.
 * 
 * @package DLParse\Core\Lexical
 * 
 * @version v0.0.1 (release)
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @license MIT
 */
abstract class Normalizer {

    /**
     * Tamaño en bytes de la cadena original a normalizar.
     * 
     * @var int $size
     */
    private readonly int $size;

    /**
     * Contenido original a ser analizado
     *
     * @var string
     */
    private readonly string $content;

    /**
     * Cursor de lectura del flujo de bytes.
     *
     * Representa el desplazamiento actual dentro del contenido interno.
     * Su valor corresponde a la posición absoluta (offset base 0) sobre
     * la secuencia de bytes analizada.
     *
     * Semánticamente actúa como puntero lógico dentro del autómata
     * de reconocimiento, permitiendo:
     *
     * - Controlar el avance determinístico del análisis.
     * - Mantener estado sin necesidad de buffers auxiliares.
     * - Evitar reinserciones o copias innecesarias.
     * - Modelar transiciones de estado mediante desplazamiento.
     *
     * Invariante:
     * 0 ≤ $cursor ≤ $this->size
     *
     * @var int
     */
    private int $cursor = 0;

    /**
     * Contenido normalizado.
     *
     * @var string
     */
    private string $normalized_content;

    /**
     * Saltos de líneas a ser normalizados
     * 
     * @var non-empty-string[]
     */
    private const BREAK_LINES = [
        // ASCII
        "\x0A",         // LF  (Unix)
        "\x0D",         // CR  (Old Mac)
        "\x0D\x0A",     // CRLF (Windows)

        // Unicode
        "\xE2\x80\xA8", // LINE SEPARATOR
        "\xE2\x80\xA9", // PARAGRAPH SEPARATOR
    ];

    /**
     * Token del salto de línea
     * 
     * @var non-empty-string
     */
    private const BREAK_LINE = "\x0A";

    /**
     * Espacios en blanco horizontales a ser normalizados.
     * 
     * NOTA:
     * - No incluye saltos de línea (ver BREAK_LINES).
     * - No incluye BOM (debe eliminarse en fase previa).
     * - Incluye espacios Unicode comúnmente introducidos por editores.
     * 
     * @var non-empty-string[]
     */
    private const WHITE_SPACES = [
        // ASCII
        "\x20", // SPACE
        "\x09", // HORIZONTAL TAB
        "\x0B", // VERTICAL TAB
        "\x0C", // FORM FEED

        // No-break y variantes
        "\xC2\xA0",       // NO-BREAK SPACE
        "\xE2\x80\xAF",   // NARROW NO-BREAK SPACE

        // Espacios históricos
        "\xE1\x9A\x80",   // OGHAM SPACE MARK

        // U+2000 — U+200A (espacios tipográficos)
        "\xE2\x80\x80",   // EN QUAD
        "\xE2\x80\x81",   // EM QUAD
        "\xE2\x80\x82",   // EN SPACE
        "\xE2\x80\x83",   // EM SPACE
        "\xE2\x80\x84",   // THREE-PER-EM SPACE
        "\xE2\x80\x85",   // FOUR-PER-EM SPACE
        "\xE2\x80\x86",   // SIX-PER-EM SPACE
        "\xE2\x80\x87",   // FIGURE SPACE
        "\xE2\x80\x88",   // PUNCTUATION SPACE
        "\xE2\x80\x89",   // THIN SPACE
        "\xE2\x80\x8A",   // HAIR SPACE

        // Separador de palabras
        "\xE2\x80\xAF",   // NARROW NO-BREAK SPACE (repetido intencionalmente si decides agrupar variantes)

        // Espacios matemáticos
        "\xE2\x81\x9F",   // MEDIUM MATHEMATICAL SPACE

        // Espacio ideográfico
        "\xE3\x80\x80",   // IDEOGRAPHIC SPACE

        // Invisibles problemáticos frecuentes en editores
        "\xE2\x80\x8B",   // ZERO WIDTH SPACE
        "\xE2\x80\x8C",   // ZERO WIDTH NON-JOINER
        "\xE2\x80\x8D",   // ZERO WIDTH JOINER
        "\xE2\x80\x8E",   // LEFT-TO-RIGHT MARK
        "\xE2\x80\x8F",   // RIGHT-TO-LEFT MARK
        "\xE2\x81\xA0",   // WORD JOINER
    ];

    /**
     * Token del espacio en blanco
     * 
     * @var non-empty-string
     */
    private const WHITE_SPACE = "\x20";

    /**
     * Valor nulo a ser eliminado
     * 
     * @var non-empty-string
     */
    private const NULL_VALUE = "\x00";

    /**
     * Retorno de de carro `\r`.
     * 
     * @var non-empty-string
     */
    private const CART_RETURN = "\x0d";

    /**
     * Tabulación horizontal.
     * 
     * @var non-empty-string
     */
    private const TAB = "\x09";

    /**
     * Bytes que serán eliminados al principio y final de la cadena de bytes. Se utilizará
     * solo tras la normalización del contenido.
     * 
     * @var non-empty-string[]
     */
    private const TRIM_CHARS = [
        self::BREAK_LINE,
        self::WHITE_SPACE,
        self::NULL_VALUE,
        self::TAB,
        self::CART_RETURN
    ];

    /**
     * Byte Order Mark
     * 
     * @var non-empty-string[]
     */
    private const BOMS = [
        "\x00\x00\xFE\xFF",
        "\xFF\xFE\x00\x00",
        "\xEF\xBB\xBF",
        "\xFE\xFF",
        "\xFF\xFE",
    ];

    /**
     * Indica si los espacios en blanco o salto de línea deben ser colapsados
     *
     * @var boolean
     */
    private readonly bool $collapse;

    public function __construct(string $content, bool $collapse = false) {
        $this->load_content($content, $collapse);
    }

    /**
     * Carga contenido en el normalizador y prepara el stream para su procesamiento.
     *
     * Este método:
     * - Define si los espacios en blanco consecutivos deben ser colapsados a uno solo,
     * - incluyendo espacios Unicode y especiales (pero excluyendo tabulaciones).
     * - Calcula el tamaño total en bytes del contenido.
     * - Remueve cualquier marcador de orden de bytes (BOM) inicial, dejando el cursor
     *    posicionado después de los BOM consecutivos.
     *
     * @package DLParse\Core\Lexical
     * @version v0.0.1 (release)
     * @author David E Luna M <dlunireframework@gmail.com>
     * @copyright (c) 2026 David E Luna M
     * @license MIT
     *
     * @param string $content Contenido crudo a cargar en el normalizador.
     * @param bool $collapse Indica si los espacios en blanco consecutivos deben colapsarse a uno solo.
     * @return void
     */
    private function load_content(string $content, bool $collapse = false): void {
        $this->content = $content;
        $this->collapse = $collapse;
        $this->size = \strlen($this->content);

        $collapse
            ? $this->normalize_content()
            : $this->remove_bom();
    }

    /**
     * Normaliza el contenido crudo
     *
     * @return void
     * @throws TokenizerException
     */
    private function normalize_content(): void {
        $this->remove_bom();

        $content = preg_replace_callback(
            pattern: $this->get_white_space_pattern(),
            callback: $this->process_match(...),
            subject: $this->normalized_content
        );

        if (!\is_string($content)) {
            throw new NormalizerException();
        }

        $content = preg_replace_callback(
            pattern: $this->get_break_line_pattern(),
            callback: $this->process_match_break_line(...),
            subject: $content
        );

        if (!\is_string($content)) {
            throw new NormalizerException();
        }

        $this->normalized_content = $this->trim($content);
    }

    /**
     * Devuelve las coincidencias procesadas
     *
     * @param array $matches Coincidencias a procesar
     * @return string
     */
    private function process_match(array $matches): string {
        /** @var string $value */
        $value = $matches[0] ?? '';

        if (!\is_string($value)) {
            $value = '';
        }

        return self::WHITE_SPACE;
    }

    /**
     * Devuelve las coincidencias procesadas en los saltos de líneas.
     *
     * @param array $matches Coincidencias a procesar.
     * @return string
     */
    private function process_match_break_line(array $matches): string {
        /** @var string $value */
        $value = $matches[0] ?? '';

        if (!\is_string($value)) {
            $value = '';
        }

        return self::BREAK_LINE;
    }

    /**
     * Devuelve la lista de caracteres que deben ser eliminados
     *
     * @return string
     */
    private function list_of_chars_to_delete(): string {
        return implode("", self::TRIM_CHARS);
    }

    /**
     * Elimina una lista de caracteres al principio y final del contenido. Esto se debe
     * hacer tras la normalización de los espacios en blanco, incluyendo saltos de línea.
     *
     * @param string $content Contenido a ser limpiado.
     * @return string
     */
    private function trim(string $content): string {
        return trim($content, $this->list_of_chars_to_delete());
    }

    /**
     * Devuelve una expresión regular de los espacios en blanco a normalizar
     *
     * @return non-empty-string
     */
    private function get_white_space_pattern(): string {
        return "/(?:" . implode("|", self::WHITE_SPACES) . ")+/";
    }
    
    /**
     * Devuelve una expresión regular de los saltos de línea a normalizar
     *
     * @return non-empty-string
     */
    private function get_break_line_pattern(): string {
        return "/(?:" . implode("|", self::BREAK_LINES) . ")+/";
    }

    /**
     * Remueve el BOM
     *
     * @return void
     */
    private function remove_bom(): void {

        foreach (self::BOMS as $bom) {
            $this->move_cursor_past_bom($bom);
        }

        $this->normalized_content = substr($this->content, $this->cursor);
    }

    /**
     * Mueve el cursor a la primera posición después de los marcadores de orden de bytes (BOM) consecutivos.
     *
     * Este método permite que el contenido pueda ser procesado a partir de la primera posición
     * libre de BOM, preservando la semántica del stream de bytes. Se detiene en el primer byte
     * que no coincide con la secuencia BOM y retrocede un byte para dejar el cursor correctamente
     * posicionado.
     *
     * @param non-empty-string $bom BOM a escanear y saltar.
     * @return void
     */
    private function move_cursor_past_bom(string $bom): void {
        if ($this->content === '')
            return;

        /** @var int $cursor */
        $cursor = 0;

        /** @var int $bom_size */
        $bom_size = \strlen($bom);

        while ($this->has_hext()) {
            /** @var non-empty-string */
            $byte = $this->consume();

            /** @var non-empty-string */
            $byte_bom = $bom[$cursor++];

            if ($cursor >= $bom_size) {
                $cursor = 0;
            }

            if ($byte !== $byte_bom) {
                $this->unconsume($cursor);
                break;
            }
        }
    }

    /**
     * Consume el byte actual y avanza el cursor una posición.
     *
     * Esta operación representa la transición elemental del autómata:
     * lectura del símbolo en la posición actual seguida del desplazamiento
     * del puntero interno hacia el siguiente byte no procesado.
     *
     * Semántica:
     * - Retorna el byte ubicado en $this->cursor.
     * - Incrementa el cursor en 1 (post-incremento).
     * - Después de la ejecución, el cursor apunta al próximo byte pendiente.
     *
     * Precondición:
     * - $this->cursor < $this->size
     *
     * Postcondición:
     * - $this->cursor aumenta exactamente en una unidad.
     * - Se preserva el invariante: 0 ≤ $this->cursor ≤ $this->size
     *
     * @return non-empty-string Byte consumido del flujo interno.
     */
    private function consume(): string {
        return $this->content[$this->cursor++];
    }

    /**
     * Revierte el cursor una o varias posiciones en el flujo de entrada.
     *
     * Deshace el último desplazamiento realizado por {@see self::consume()},
     * retrocediendo exactamente `$cursor` posiciones si el cursor lo permite.
     *
     * Esta operación es segura frente a underflow: si el retroceso propuesto
     * dejaría el cursor en una posición negativa, se lanza una excepción.
     *
     * Complejidad temporal: O(1).
     *
     * @param int $cursor Número de posiciones a retroceder (por defecto 1).
     * @throws NormalizerException Si el retroceso dejaría el cursor en una posición negativa.
     *
     * @return void
     */
    private function unconsume(int $cursor = 1): void {
        /** @var int $value */
        $value = \intval($this->cursor - abs($cursor));

        if ($value < 0) {
            throw new NormalizerException("El retroceso solicitado excede la posición inicial del cursor.");
        }

        $this->cursor -= $cursor;
    }

    /**
     * Determina si existe al menos un byte pendiente por consumir.
     *
     * Evalúa si el cursor interno aún no ha alcanzado el tamaño total
     * del contenido, lo que implica que el autómata puede realizar
     * una transición de consumo segura.
     *
     * Semántica:
     * - Retorna true si el cursor apunta a una posición válida dentro
     *   del flujo de bytes.
     * - Retorna false si el cursor ha alcanzado o superado el límite,
     *   indicando que no quedan símbolos por procesar.
     *
     * Invariante relacionado:
     * 0 ≤ $this->cursor ≤ $this->size
     *
     * Esta función debe utilizarse como verificación previa a la
     * invocación de consume(), garantizando el cumplimiento de su
     * precondición.
     *
     * @return bool true si hay bytes pendientes; false en caso contrario.
     */
    private function has_hext(): bool {
        return $this->cursor < $this->size;
    }

    /**
     * Devuelve el contenido normalizado
     *
     * @return string
     */
    protected function get_normalized_content(): string {
        return $this->normalized_content;
    }
}