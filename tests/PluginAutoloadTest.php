<?php

namespace Ninja\Composer\Preload\Tests;

use Ninja\Composer\Preload\Composer\Command\PreloadCommand;
use Ninja\Composer\Preload\Composer\Command\PreloadCommandProvider;
use Ninja\Composer\Preload\Composer\Plugin;
use PHPUnit\Framework\TestCase;

class PluginAutoloadTest extends TestCase {

	public function testAutoload(): void {
		$this->assertTrue(class_exists(Plugin::class));
		$this->assertTrue(class_exists(PreloadCommandProvider::class));
		$this->assertTrue(class_exists(PreloadCommand::class));
	}
}
