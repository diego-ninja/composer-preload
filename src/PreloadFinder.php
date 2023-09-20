<?php


namespace Ninja\Composer\Preload;

use BadMethodCallException;
use InvalidArgumentException;
use Iterator;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use const PREG_NO_ERROR;

class PreloadFinder {
    private array $include_dirs = [];
    private array $include_files = [];
    private array $exclude_dirs = [];
    private array $exclude_sub_dirs = [];
    private array $exclude_regex_static = [];
    private array $files = ['php'];

    private Finder $finder;

    private ?string $exclude_regex = null;

    public function __construct() {
        $this->finder = new Finder();
    }

    public function getIterator(): Iterator {
        $this->prepareFinder();
        return $this->finder->getIterator();
    }

    private function prepareFinder(): void {
        if (empty($this->include_dirs)) {
            throw new BadMethodCallException(
                'Illegal attempt to get iterator without setting include directory list.'
            );
        }

        foreach ($this->files as $extension) {
            $this->finder->files()->name('*.' . $extension);
        }

        $this->finder->in($this->include_dirs);

        if ($this->exclude_sub_dirs) {
            $this->finder->exclude($this->exclude_sub_dirs);
        }

        $exclude_function = $this->getExcludeCallable();
        if ($exclude_function !== null) {
            $this->finder->filter($exclude_function);
        }

        // include_files
        if ($include_files = $this->include_files) {
            $this->finder->append($include_files);
        }

    }

    private function getExcludeCallable(): ?callable {
        $regex_dir = $this->getDirectoryExclusionRegex();
        $regex_static = $this->exclude_regex_static;

        if (!$regex_dir && $this->exclude_regex_static === []) {
            return null;
        }

        return static function (SplFileInfo $file) use ($regex_dir, $regex_static): bool {
            $path = str_replace('\\', '/', $file->getPathname());
            $exclude_match = false;
            if ($regex_dir) {
                $exclude_match = preg_match($regex_dir, $path);
            }

            // If excluded due to directory match above , don't run the static regex.
            if (!$exclude_match && $regex_static) {
                foreach ($regex_static as $regex_static_item) {
                    $exclude_match = preg_match($regex_static_item, $path);
                    if ($exclude_match) {
                        break;
                    }
                }
            }

            return !$exclude_match;
        };
    }

    protected function getDirectoryExclusionRegex(): ?string {
        if ($this->exclude_regex !== null) {
            return $this->exclude_regex;
        }

        if (empty($this->exclude_dirs)) {
            return null;
        }

        $regex = '/^(';
        $dirs = [];
        foreach ($this->exclude_dirs as $dir) {
            $dir = str_replace('\\', '/', $dir);
            if (!str_ends_with($dir, '/')) {
                $dir .= '/'; // Force all directives to be full direcory paths with "/" suffix.
            }
            $dir = preg_quote($dir, '/');
            $dirs[] = $dir;
        }
        $regex .= implode('|', $dirs);
        $regex .= ')/i';

        $this->exclude_regex = $regex;
        return $regex;
    }

    public function addIncludeFile(string $file_name): void {
        $this->include_files[] = new SplFileInfo($file_name);
    }

    public function addIncludePath(string $dir_name): void {
        $this->include_dirs[] = $dir_name;
    }

    public function addExcludePath(string $dir_name): void {
        $this->exclude_regex = null;
        $this->exclude_dirs[] = $dir_name;
    }

    public function addExcludeDirPattern(string $dir_name): void {
        $this->exclude_sub_dirs[] = $dir_name;
    }

    public function setExcludeRegex(?array $patterns): void {
        if (null !== $patterns) {
            // A parent error handler might catch the errors.
            foreach ($patterns as $pattern) {
                preg_match($pattern, '', $fake_matched);
                $regex_error = preg_last_error();
                if ($regex_error !== PREG_NO_ERROR) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Preload exclusion regex is invalid: "%s". Error code: %d',
                            $pattern,
                            $regex_error
                        ),
                        $regex_error
                    );
                }

                $this->exclude_regex_static[] = $pattern;
            }
        }
    }

    public function addIncludeExtension(string $extension): void {
        if (preg_match('/[^A-z0-9]/', $extension) !== 0) {
            throw new InvalidArgumentException(sprintf('File extension is not valid: "%s"', $extension));
        }

        $this->files[] = $extension;
        $this->files = array_unique($this->files);
    }
}
