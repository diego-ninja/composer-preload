<?php


namespace Ninja\Composer\Preload;


use BadMethodCallException;
use RuntimeException;
use SplFileInfo;

class PreloadWriter {

    public const MECHANISM_REQUIRE = 'require';
    public const MECHANISM_COMPILE = 'opcache_compile_file';
    private int $count;
    private bool $status_check = true;
    private string $filename = 'vendor/preload.php';

    public function __construct(private PreloadList $list, private string $mechanism) {
        $this->list = $list;
    }

    public function setPath(string $path): void {
        $this->filename = $path;
    }

    public function getPath(): string {
        return $this->filename;
    }

    public function write(): void {
        $status = file_put_contents($this->filename, $this->getScript());
        if (!$status) {
            throw new RuntimeException('Error writing the preload file.');
        }
    }

    public function getScript(): string {
        $this->count = 0;
        $list = $this->getHeader();

        if ($this->status_check) {
            $list .= $this->getStatusCheck();
        }

        $list .= '// Cache files to opcache.' . PHP_EOL;
        foreach ($this->list as $file) {
            /**
             * @var $file SplFileInfo
             */
            $list .= $this->genCacheLine($file->getPathname());
            ++$this->count;
        }

        return $list;
    }

    private function getHeader(): string {
        return <<< HEADER
<?php 

/**
 * Opcache warm-up file generated by Composer Preload plugin.
 * This file was generated automatically. Any changes will be overwritten
 * during the next "composer preload" command. 
 */

require_once(\dirname(__DIR__) . '/vendor/autoload.php');

\$_root_directory = \dirname(__DIR__);

HEADER;
    }

    private function getStatusCheck(): string {
        return <<<CHECK

if (!\\function_exists('opcache_compile_file') || !\ini_get('opcache.enable')) {
  echo 'Opcache is not available.';
  die(1);
}

if ('cli' === \PHP_SAPI && !\ini_get('opcache.enable_cli')) {
  echo 'Opcache is not enabled for CLI applications.';
  die(2);
}


CHECK;
    }

    private function genCacheLine(string $file_path): string {
        $file_path = str_replace(DIRECTORY_SEPARATOR, '/', $file_path);
        $file_path = addslashes($file_path);
        if ($this->mechanism === self::MECHANISM_REQUIRE) {
            return "require_once(\$_root_directory . '/{$file_path}');" . PHP_EOL;
        }


        return "\opcache_compile_file(\$_root_directory . '/{$file_path}');" . PHP_EOL;
    }

    public function getCount(): int {
        if ($this->count === null) {
            throw new BadMethodCallException('File count is not available until iterated.');
        }
        return $this->count;
    }

    public function setStatusCheck(bool $check): void {
        $this->status_check = $check;
    }
}
