<?php
declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

use DLParse\Core\Lexical\Normalizer as BaseNormalizer;
use DLParse\Exceptions\NormalizerException;

final class Normalizer extends BaseNormalizer {

    public function __construct(string $content) {
        parent::__construct($content, true);
    }

    /**
     * Devuelve el contenido normalizado
     *
     * @param string $filename Archivo a ser almacenado.
     * @return void
     */
    public function format_document(string $filename): void {
        file_put_contents($filename, $this->get_normalized_content());
    }
}

final class Filename {

    // public static function 

    /**
     * Devuelve el nombre de archivo
     *
     * @return string
     */
    public static function get_filename(): string {

        /** @var string|null $filename */
        $filename = $argv[1] ?? null;

        if ($filename === null || trim($filename) === '') {
            throw new NormalizerException("El nombre de archivo es requerido");
        }

        return $filename;
    }

    /**
     * Devuelve el contenido del archivo seleccionado.
     *
     * @return string
     */
    public static function get_content(): string {

        /** @var non-empty-string */
        $filename = self::get_filename();

        if (!file_exists($filename)) {
            throw new NormalizerException("El archivo que intenta corregir no existe", 404);
        }

        /** @var bool|string */
        $content = file_get_contents($filename);

        if ($content === FALSE) {
            return "";
        }

        return $content;
    }
}

$content = Filename::get_content();

$normalizer = new Normalizer($content);
$normalizer->format_document(Filename::get_filename());

print_r($argv);
exit;