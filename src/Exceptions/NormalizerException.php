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

namespace DLParse\Exceptions;

use Exception;

/**
 * NormalizerException
 *
 * Se lanza cuando ocurre un error durante la fase de normalización
 * del input antes del análisis léxico.
 *
 * Ejemplos:
 * - Secuencia Unicode inválida
 * - Codificación no soportada
 * - Byte Order Mark (BOM) corrupto
 * - Secuencia de bytes ilegible o inconsistente
 * - Transformación de preprocesamiento fallida
 *
 * Uso típico: normalizadores de entrada, preprocesadores,
 * canonicalizadores y etapas previas a la tokenización.
 *
 * @package DLParse\Exceptions
 * @version v0.0.1
 * @license MIT
 * @author David E Luna M
 * @copyright Copyright (c) 2026 David E Luna M
 */
final class NormalizerException extends Exception {
    /**
     * @param string          $message  Mensaje descriptivo del error de normalización
     * @param int             $code     Código de error (0 por defecto)
     * @param \Throwable|null $previous Excepción previa (encadenamiento)
     */
    public function __construct(
        string $message = 'Error durante la normalización del input.',
        int $code = 500,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}