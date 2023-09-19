<?php

namespace Ninja\Composer\Preload\Tests;

use BadMethodCallException;
use Ninja\Composer\Preload\PreloadList;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;

class PreloadListTest extends TestCase {

    /**
     * @throws \Exception
     */
    public function testSetList(): void {
		$iterator = new \ArrayIterator(['test' => base64_encode(\random_bytes(12))]);
		$list     = new PreloadList();
		$list->setList($iterator);

		$this->expectException(TypeError::class);
		$list->setList(new stdClass());
	}

    /**
     * @throws \Exception
     */
    public function testGetIterator(): void {
		$iterator = new \ArrayIterator(['test' => base64_encode(\random_bytes(12))]);
		$list     = new PreloadList();
		$list->setList($iterator);
		$this->assertSame($iterator, $list->getIterator());

		$list = new PreloadList();
		$this->expectException(BadMethodCallException::class);
		$list->getIterator();
	}
}
