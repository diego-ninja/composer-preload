<?php

namespace Ninja\Composer\Preload;

use BadMethodCallException;
use IteratorAggregate;
use Traversable;

final class PreloadList implements IteratorAggregate {

    private ?Traversable $list = null;

    public function setList(Traversable $list): void {
        $this->list = $list;
    }

    public function getIterator(): Traversable {
        if (!$this->list) {
            throw new BadMethodCallException('Attempting to fetch the iterator without setting one first.');
        }
        return $this->list;
    }
}
