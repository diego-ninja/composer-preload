<?php


namespace Ninja\Composer\Preload\Composer;


use Ninja\Composer\Preload\Composer\Command\PreloadCommandProvider;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;

class Plugin implements PluginInterface, Capable {
    /**
     * Apply plugin modifications to Composer
     *
     * @param Composer $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io): void {

    }

    public function getCapabilities(): array {
        return array(
            CommandProvider::class => PreloadCommandProvider::class,
        );
    }

    /**
     * @inheritDoc
     */
    public function deactivate(Composer $composer, IOInterface $io) {
    }

    /**
     * @inheritDoc
     */
    public function uninstall(Composer $composer, IOInterface $io) {
    }
}
