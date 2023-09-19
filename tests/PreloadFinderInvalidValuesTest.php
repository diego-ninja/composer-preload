<?php

namespace Ninja\Composer\Preload\Tests;

use BadMethodCallException;
use Ninja\Composer\Preload\PreloadFinder;
use PHPUnit\Framework\TestCase;

class PreloadFinderInvalidValuesTest extends TestCase {

	public function testGetIteratorInvalidState(): void {
		$finder = new PreloadFinder();
		$this->expectException(BadMethodCallException::class);
		$finder->getIterator();
	}
}
