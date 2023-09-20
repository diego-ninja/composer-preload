<?php


namespace Ninja\Composer\Preload;


class PreloadGenerator {
    private PreloadFinder $finder;

    public function __construct() {
        $this->finder = new PreloadFinder();
    }

    public function getList(): PreloadList {
        $list = new PreloadList();
        $list->setList($this->finder->getIterator());
        return $list;
    }

    public function addPath(string $path): void {
        $this->finder->addIncludePath($path);
    }

    public function addFile(string $file): void {
        $this->finder->addIncludeFile($file);
    }

    public function addExcludePath(string $path): void {
        $this->finder->addExcludePath($path);
    }

    public function setExcludeRegex(array $patterns): void {
        $this->finder->setExcludeRegex($patterns);
    }

    public function addIncludeExtension(string $extension): void {
        $this->finder->addIncludeExtension($extension);
    }
}
