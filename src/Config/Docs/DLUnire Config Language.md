# **DLUnire Config Language (DLC)**

**Tipado, declarativo y confiable para la configuración de aplicaciones DLUnire**

---

## 🔹 Descripción / Description

**ES:**
DLUnire Config Language (DLC) es un **lenguaje de configuración tipado** y declarativo para aplicaciones DLUnire. Permite definir configuraciones explícitas, seguras y dinámicas, con soporte para tipos primitivos (`string`, `boolean`, `integer`, `float`, `numeric`, `email`, `uuid`) y estructuras de inicialización automáticas mediante un **bootloader entrópico**.

**EN:**
DLUnire Config Language (DLC) is a **typed, declarative configuration language** for DLUnire applications. It allows defining explicit, safe, and dynamic configurations, supporting primitive types (`string`, `boolean`, `integer`, `float`, `numeric`, `email`, `uuid`) and automatic initialization through an **entropic bootloader**.

---

## 🔹 Tipos soportados y valores por defecto / Supported Types and Defaults

| Tipo / Type | Descripción / Description            | Valor por defecto / Default | Ejemplo / Example                                   |
| ----------- | ------------------------------------ | --------------------------- | --------------------------------------------------- |
| `string`    | Cadena de texto / Text string        | `""`                        | `APP_NAME: string = "MiApp"`                        |
| `boolean`   | Verdadero/Falso / True/False         | `false`                     | `DEBUG: boolean = true`                             |
| `integer`   | Número entero / Integer number       | `0`                         | `PORT: integer = 3306`                              |
| `float`     | Número decimal / Floating point      | `0.0`                       | `PI: float = 3.1415`                                |
| `numeric`   | Entero o flotante / Integer or float | `0`                         | `VALOR: numeric = 100.3`                            |
| `email`     | Correo válido / Valid email          | `""`                        | `MAIL_CONTACT: email = contact@example.com`         |
| `uuid`      | Identificador único / UUID           | Generado automáticamente    | `UUID: uuid = c61cc834-5957-11ee-9db5-0023ae88eef0` |

---

## 🔹 Sintaxis básica / Basic Syntax

### Comentarios / Comments

* `/** … */` para comentarios de bloque.
* `#` para comentarios de línea.

**Ejemplo / Example:**

```envtype
/**
 * Indica si la aplicación está en modo producción.
 */
DL_PRODUCTION: boolean = false

# Servidor de base de datos
DL_DATABASE_HOST: string = "localhost"
```

---

### Declaración de variables / Variable Declaration

```envtype
NOMBRE_VARIABLE: TIPO = VALOR
```

* `TIPO` debe coincidir con el tipo soportado.
* `VALOR` debe respetar el tipo.

**Ejemplo / Example:**

```envtype
APP_NAME: string = "MiApp"
DEBUG: boolean = true
PORT: integer = 8080
PI: float = 3.1415
VALOR: numeric = 100.3
MAIL_CONTACT: email = no-reply@example.com
UUID: uuid = c61cc834-5957-11ee-9db5-0023ae88eef0
```

---

## 🔹 Convención de nombres de archivo / File Naming Convention

1. **Extensión larga (recomendada para entornos completos)**

```text
{nombre-de-archivo}.env.type
```

* Ejemplos:

```text
app.env.type
database.env.type
```

2. **Extensión corta (opcional para módulos o subcomponentes)**

```text
{nombre-de-archivo}.type
```

* Ejemplos:

```text
app.type
database.type
```

> 💡 Ambos formatos son compatibles con el parser `dlcparse`.

---

## 🔹 Ejemplo de configuración completa / Full Example

```envtype
# Modo producción
DL_PRODUCTION: boolean = false

# Base de datos
DL_DATABASE_HOST: string = "localhost"
DL_DATABASE_PORT: integer = 3306
DL_DATABASE_USER: string = "usuario"
DL_DATABASE_PASSWORD: string = "contraseña"
DL_DATABASE_NAME: string = "mi_base"
DL_DATABASE_CHARSET: string = "utf8"
DL_DATABASE_COLLATION: string = "utf8_general_ci"
DL_DATABASE_DRIVE: string = "mysql"
DL_PREFIX: string = "mi_prefijo_"

# Correo electrónico
MAIL_HOST: string = "smtp.mi-hosting.com"
MAIL_USERNAME: email = no-reply@example.com
MAIL_PASSWORD: string = "mi-contraseña"
MAIL_PORT: integer = 465
MAIL_COMPANY_NAME: string = "MiEmpresa"
MAIL_CONTACT: email = contact@example.com

# Google reCAPTCHA (opcional)
G_SECRET_KEY: string = "<tu-llave-privada>"
G_SITE_KEY: string = "<tu-llave-del-sitio>"

# Identificador único
UUID: uuid = c61cc834-5957-11ee-9db5-0023ae88eef0
```

---

## 🔹 Reglas / Rules

* Los nombres de variables **deben ser únicos** por archivo.
* El **tipo de dato debe coincidir estrictamente** con el valor.
* Comentarios permiten al **bootloader entrópico** generar formularios dinámicos y prevenir errores.
* Para números se puede usar `numeric`, `integer` o `float` según corresponda.

---

## 🔹 Convenciones de estilo / Style Conventions

* Variables **mayúsculas con guión bajo** (`DL_DATABASE_HOST`).
* Comentarios de bloque `/** … */` para documentación importante.
* Archivos terminan con extensión **`.env.type`** o **`.type`**.
* El **bootloader** ejecuta lógica solo si la configuración cambia de directorio o valores críticos (`FILE_PATH`, `DATABASE`, etc.).

---

## 🔹 Ejemplo de bootloader / Bootloader Example

```php
use dlunire\Config\Bootloader\Boot;

$configFile = "app.env.type";
$boot = new Boot($configFile);

// Ejecuta inicialización dinámica solo si la configuración cambió
$boot->run();
```

---

## 🔹 Ejemplo CLI / CLI Example

```bash
# Validar archivo de configuración
dlccli validate app.env.type

# Ejecutar bootloader
dlccli boot app.env.type

# Generar plantilla de configuración por módulo
dlccli generate database.type
```

---

## 🔹 Organización recomendada del repositorio / Recommended Repository Structure

```text
dlparse/
├─ src/
│   └─ Config/
│       ├─ Parser/      # dlcparse
│       ├─ Bootloader/  # dlcboot
│       ├─ Core/        # dlccore
│       └─ CLI/         # dlccli
├─ examples/            # Ejemplos de archivos .env.type y .type
├─ tests/               # Pruebas unitarias e integración
├─ README.md            # Documentación oficial
├─ LICENSE              # MIT
└─ composer.json        # Autoload PSR-4 si aplica
```