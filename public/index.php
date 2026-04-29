<?php
declare(strict_types=1);
ini_set('display_errors', 1);

use DLParse\Core\Config\Parser\TypedEnvironmentLexer;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

class Test extends TypedEnvironmentLexer {

    /**
     * Contenido de archivo a ser analizado
     * 
     * @var string|null
     */
    private ?string $file_content = null;

    public function __construct(string $content) {
        $this->load_content();
        parent::__construct(content: $this->file_content ?? $content, normalize: false);
    }

    /**
     * Carga el contenido del archivo para procesarlo con el lexer.
     * 
     * @return void 
     */
    public function load_content(): void {

        /** @var non-empty-string $filename */
        $filename = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'test.type';

        if (!file_exists($filename)) {
            return;
        }

        /** @var string|bool $content */
        $content = file_get_contents($filename);

        if ($content === false) {
            return;
        }

        $this->file_content = $content;
    }
}

header("content-type: text/plain; charset=utf-8", true, 200);

$start = hrtime(true);
$test = new Test("/** NO HAY CÓDIGO AQUÍ */");
$test->scan();
$end = hrtime(true);

$total = $end - $start;

print_r("\n\$total: {$total}");