<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers;

use Sunnysideup\UpgradeToSilverstripe4\Traits\HelperInst;

class Composer
{
    use HelperInst;

    protected $defaultOptions = '';

    public function DumpAutoload(): self
    {
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'composer dumpautoload',
            'run composer dumpautoload',
            false
        );

        return $this;
    }

    public function ClearCache(): self
    {
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'composer clear-cache',
            'clear composer cache',
            false
        );

        return $this;
    }

    public function RequireGlobal(string $package, ?string $version = '', ?bool $devOnly = false, ?string $options = ''): self
    {
        return $this->requireAny($package, $version, $devOnly, $options, true);
    }

    public function Require(string $package, ?string $version = '', ?bool $devOnly = false, ?string $options = ''): self
    {
        return $this->requireAny($package, $version, $devOnly, $options, false);
    }

    protected function requireAny(string $package, ?string $version = '', ?bool $devOnly = false, ?string $options = '', ?bool $isGlobal = false): self
    {
        $devFlag = $devOnly ? '--dev' : '';
        if (! $options) {
            $options = $this->defaultOptions;
        }
        if ($version) {
            $version = ':' . $version;
        }
        $globalPhrase = '';
        if ($isGlobal) {
            $globalPhrase = 'global';
        }
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'composer ' . $globalPhrase . ' require ' . $package . $version . ' ' . $devFlag . ' ' . $options,
            'running composer require ' . $package . $version . ' ' . $devFlag . ' ' . $options,
            false
        );

        return $this;
    }
}
