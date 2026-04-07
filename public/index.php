<?php
declare(strict_types=1);
ini_set('display_errors', 1);

use DLParse\Core\Config\Parser\TypedEnvironmentLexer;

require_once dirname(__DIR__) . "/vendor/autoload.php";

class Test extends TypedEnvironmentLexer {

    public function __construct(string $content) {
        parent::__construct($content, false);
    }
}

/** @var non-empty-string $content */
$content = <<<BASH
# Indica \\x0Asi \\n la aplicación debe correr o no en producción - Esa es otra prueba:
DL_PRODUCTION: boolean = false




# Servidor de la base de datos // Un comentario interno:
DL_DATABASE_HOST: string = "localhost"

# Puerto del motor de la base de datos:
DL_DATABASE_PORT: integer = 3306

# Usuario de la base de datos:
DL_DATABASE_USER: string = "tu-usuario"

# Contraseña de la base de datos:
DL_DATABASE_PASSWORD: string = "tu-contraseña"

# Nombre de la base de datos:
DL_DATABASE_NAME: string = "tu-base-de-datos"

# Codificación de caracteres de la base de datos. Si no se define, 
# entonces, el valor por defecto serà `utf8`:
DL_DATABASE_CHARSET: string = "utf8"

# Colación del motor de base de datos. Si no se define, el valor por
# defecto será `utf8_general_ci`:
DL_DATABASE_COLLATION: string = "utf8_general_ci"

# Motor de base de datos. Si no se define esta variable, el valor
# por defecto será `mysql`:
DL_DATABASE_DRIVE: string = "mysql"

# Si la base de datos usa prefijo, entonces debe declararla aquí:
DL_PREFIX: string = "tu-prefijo_"

# Servidor SMTP de tu hosting para configurar un correo electrónico:
MAIL_HOST: string = "smtp.tu-hosting.com"

# Usuario de correo electrónico. Tome en cuenta que no debe colocar
# comillas de ningún tipo, porque no se evaluaría si es un correo:
MAIL_USERNAME: email = no-reply@example.com

# Contraseña del correo electrónico:
MAIL_PASSWORD: string = "Contraseña de correo"

# Puerto del servidor SMTP con certificado SSL para tu correo 
# electrónico:
MAIL_PORT: integer = 465

# Nombre de la empresa que envía el correo a través de su web
# o aplicación:
MAIL_COMPANY_NAME: string = "Empresa, marca o tu marca personal"

# Correo de contacto:
MAIL_CONTACT: email = contact@example.com

# Estas variables son opcionales. Si desea establecer un reCAPTCHA
# de Google, puedes definirlas aquí:
G_SECRET_KEY: string = "<tu-llave-privada>"
G_SITE_KEY: string = "<tu-llave-del-sitio>"

# Si deseas establecer un identificador de tipo UUID la puedes definir
# en la siguiente línea. Tome en cuenta que no debe tener comillas
# para que pueda ser evaluado si se trata de un UUID válido.
UUID: uuid = c61cc834-5957-11ee-9db5-0023ae88eef0

/**
 * Si quieres permitir un número, debes definirla de este modo:
 * 
 * ```envtype
 * NUMERO: numeric = 100
 * ```
 * // david eduardo
 * O de esta otra forma:
 * 
 * ```envtype
 * NUMERO: numeric = 100.3
 * ```
 * Puede definir un entero o flotante así:
 * 
 * ```envtype
 *  FLOTANTE: float = 100.3
 *
 *  ENTERO: integer = 100
 * ```
 *
 */

   # David
   // David
/** Algo por aquí */
BASH;

header("content-type: text/plain; utf-8", true, 200);

// $content = "IDENTIFICADOR: tipo = value\x0aOTRO_IDENTIFICADOR: tipo = \"otro valor\"\x0a   # ciencia";
$start = microtime(true);
$test = new Test($content);
$test->scan();
$end = microtime(true);

$total = $end - $start;

print_r("\n\$total: {$total}");