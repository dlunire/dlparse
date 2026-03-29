<?php

declare(strict_types=1);

namespace DLParse\Core\Config\Parser\Enums;

/**
 * Enumeración de tipos de error para el parser de configuración de DLParse.
 *
 * Define todos los tipos de errores que pueden ocurrir durante el análisis sintáctico (parsing)
 * de archivos de configuración. Cada caso contiene un mensaje de error plantilla con marcadores
 * de posición (:token:, :column:, :line:) que serán reemplazados con valores reales durante
 * el procesamiento del autómata léxico.
 *
 * ## Flujo sintáctico esperado
 * La sintaxis esperada sigue el patrón:
 * ```
 * IDENTIFICADOR: tipo = valor
 * ```
 *
 * Donde:
 * - **IDENTIFICADOR**: Comienza con mayúscula (A-Z), contiene solo A-Z y guiones bajos (_)
 * - **:**: Separador de tipo (obligatorio)
 * - **tipo**: Solo letras minúsculas (a-z)
 * - **=**: Operador de asignación (obligatorio)
 * - **valor**: Valor a asignar
 *
 * ## Ejemplo de uso
 * ```php
 * use DLParse\Core\Config\Parser\Enums\ErrorType;
 *
 * // Obtener mensaje de error formateado (usando autómata léxico)
 * $error = ErrorType::IDENTIFIER_TOKEN;
 * $message = $error->value; // Acceder al mensaje plantilla
 *
 * // El autómata se encargará de reemplazar los marcadores:
 * // "Token inválido 'userName' en la columna 10, línea 5. ..."
 * ```
 *
 * @package DLParse\Core\Config\Parser\Enums
 * @since 1.0.0
 * @author DLParse Team
 */
enum ErrorType: string {

    /**
     * Sin errores / Estado neutro.
     *
     * Representa la ausencia de error o un estado de validación exitoso.
     * Se utiliza como valor por defecto o cuando no hay problemas en el parsing.
     *
     * @var string Mensaje "No se encontraron errores"
     */
    case NULL = 'No se encontraron errores';

    /**
     * Token inesperado detectado.
     *
     * Se dispara cuando el autómata encuentra un token que no es válido en el contexto
     * actual del parsing. Este es el error más genérico y ocurre cuando se encuentra
     * cualquier carácter o secuencia que no encaja en la sintaxis esperada.
     *
     * **Marcadores de posición:**
     * - `:token:` - El token problemático encontrado
     * - `:column:` - Número de columna donde ocurrió el error (relativo a la línea actual)
     * - `:line:` - Número de línea donde ocurrió el error
     *
     * **Ejemplo de salida:**
     * ```
     * Token } inesperado en la columa 45, línea 12
     * ```
     *
     * @var string Mensaje de error para token inesperado
     * @see IDENTIFIER_TOKEN Para errores específicos de identificadores
     * @see TYPE_TOKEN Para errores específicos de tipos
     */
    case UNEXPECTED_TOKEN = "Token :token: inesperado en la columa :column:, línea :line:";

    /**
     * Identificador inválido detectado.
     *
     * Se dispara cuando un identificador no cumple con las reglas de validación establecidas.
     * Los identificadores deben:
     * - Comenzar con una letra mayúscula (A-Z)
     * - Contener únicamente letras mayúsculas (A-Z) o guiones bajos (_)
     * - No contener números, minúsculas u otros caracteres
     *
     * **Marcadores de posición:**
     * - `:token:` - El identificador problemático encontrado
     * - `:column:` - Número de columna donde ocurrió el error
     * - `:line:` - Número de línea donde ocurrió el error
     *
     * **Ejemplos de tokens inválidos:**
     * - `userName` (comienza con minúscula)
     * - `user_name_123` (contiene números)
     * - `user-name` (contiene guiones en lugar de guiones bajos)
     * - `USER_NAME` (válido ✓)
     *
     * **Ejemplo de salida:**
     * ```
     * Token inválido 'userName' en la columna 10, línea 5. Los identificadores deben
     * empezar con una letra mayúscula (A-Z) y contener solo letras mayúsculas o guiones bajos.
     * ```
     *
     * @var string Mensaje detallado de error de identificador
     * @see TYPE_TOKEN Para validación de tipos
     */
    case IDENTIFIER_TOKEN = "Token inválido ':token:' en la columna :column:, línea :line:. Los identificadores deben empezar con una letra mayúscula (A-Z) y contener solo letras mayúsculas o guiones bajos.";

    /**
     * Token de tipo inválido detectado.
     *
     * Se dispara cuando un tipo no cumple con las reglas de validación establecidas.
     * Los tipos deben:
     * - Contener **únicamente** letras minúsculas (a-z)
     * - No contener números, mayúsculas u otros caracteres
     *
     * **Marcadores de posición:**
     * - `:token:` - El tipo problemático encontrado
     * - `:column:` - Número de columna donde ocurrió el error
     * - `:line:` - Número de línea donde ocurrió el error
     *
     * **Ejemplos de tokens inválidos:**
     * - `String` (contiene mayúscula)
     * - `string123` (contiene números)
     * - `string_type` (contiene guiones bajos) [depende de reglas específicas]
     * - `string` (válido ✓)
     *
     * **Ejemplo de salida:**
     * ```
     * Token de tipo inválido 'string' en la columna 20, línea 8. Los tipos deben
     * contener únicamente letras minúsculas (a-z).
     * ```
     *
     * @var string Mensaje detallado de error de tipo
     * @see IDENTIFIER_TOKEN Para validación de identificadores
     */
    case TYPE_TOKEN = "Token de tipo inválido ':token:' en la columna :column:, línea :line:. Los tipos deben contener únicamente letras minúsculas (a-z).";

    /**
     * Separador de tipo inválido o faltante.
     *
     * Se dispara cuando falta el token ':' (dos puntos) entre el identificador y el tipo,
     * o cuando existe pero está en una posición incorrecta o es un carácter diferente.
     *
     * La estructura esperada es: `IDENTIFICADOR:tipo=valor`
     *
     * **Marcadores de posición:**
     * - `:token:` - El token encontrado en lugar del ':' esperado
     * - `:column:` - Número de columna donde ocurrió el error
     * - `:line:` - Número de línea donde ocurrió el error
     *
     * **Ejemplos de tokens inválidos:**
     * - `USER_NAME=string` (falta ':')
     * - `USER_NAME=string=valor` (hay '=' en lugar de ':')
     * - `USER_NAME string` (hay espacio en lugar de ':')
     *
     * **Ejemplo de salida:**
     * ```
     * Token de separador de tipo inválido '=' en la columna 15, línea 3.
     * Se esperaba ':' entre identificador y tipo.
     * ```
     *
     * @var string Mensaje detallado de error de separador
     * @see ASSIGN_TOKEN Para validación del operador de asignación
     */
    case COLON_TOKEN = "Token de separador de tipo inválido ':token:' en la columna :column:, línea :line:. Se esperaba ':' entre identificador y tipo.";

    /**
     * Operador de asignación inválido o faltante.
     *
     * Se dispara cuando falta el token '=' (igual) entre el tipo y el valor,
     * o cuando existe pero está en una posición incorrecta o es un carácter diferente.
     *
     * La estructura esperada es: `IDENTIFICADOR:tipo=valor`
     *
     * **Marcadores de posición:**
     * - `:token:` - El token encontrado en lugar del '=' esperado
     * - `:column:` - Número de columna donde ocurrió el error
     * - `:line:` - Número de línea donde ocurrió el error
     *
     * **Ejemplos de tokens inválidos:**
     * - `USER_NAME:string valor` (falta '=', hay espacio)
     * - `USER_NAME:string: valor` (hay ':' en lugar de '=')
     * - `USER_NAME:string+valor` (hay '+' en lugar de '=')
     *
     * **Ejemplo de salida:**
     * ```
     * Token de asignación inválido ':' en la columna 30, línea 7.
     * Se esperaba '=' para asignación.
     * ```
     *
     * @var string Mensaje detallado de error de asignación
     * @see COLON_TOKEN Para validación del separador de tipo
     */
    case ASSIGN_TOKEN = "Token de asignación inválido ':token:' en la columna :column:, línea :line:. Se esperaba '=' para asignación.";
}