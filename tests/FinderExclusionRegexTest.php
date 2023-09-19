<?php

namespace Ninja\Composer\Preload\Tests;

use Ninja\Composer\Preload\PreloadFinder;
use PHPUnit\Framework\TestCase;

class FinderExclusionRegexTest extends TestCase {

	public function testRegex(): void {
		$class  = $this->getMockupRegexBuilder();

        $finder = new $class();

		$this->assertNull($finder->getDirectoryExclusionRegex());

		$finder->addExcludePath('test');
		$this->assertSame('/^(test\/)/i', $finder->getDirectoryExclusionRegex());

		$finder->addExcludePath('test2');
		$this->assertSame('/^(test\/|test2\/)/i',
			$finder->getDirectoryExclusionRegex());

		$finder->addExcludePath('src\\');
		$this->assertSame('/^(test\/|test2\/|src\/)/i',
			$finder->getDirectoryExclusionRegex());

		$finder->addExcludePath('src/');
		$this->assertSame('/^(test\/|test2\/|src\/|src\/)/i',
			$finder->getDirectoryExclusionRegex());
	}

	public function getMockupRegexBuilder(): PreloadFinder {
		return new class() extends PreloadFinder {

			public function getDirectoryExclusionRegex(): ?string {
				return parent::getDirectoryExclusionRegex();
			}
		};
	}
}
