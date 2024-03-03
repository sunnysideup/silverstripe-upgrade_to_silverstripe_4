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
            'composer dumpautoload --no-interaction',
            'run composer dumpautoload',
            false
        );

        return $this;
    }

    public function ClearCache(): self
    {
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'composer clear-cache --no-interaction',
            'clear composer cache',
            false
        );

        return $this;
    }

    public function RequireGlobal(string $package, ?string $version = '', ?bool $devOnly = false, ?string $options = ''): self
    {
        return $this->requireAny($package, $version, $devOnly, $options, true);
    }

    public function Require(string $package, ?string $version = '', ?string $options = ''): self
    {
        return $this->requireAny($package, $version, false, $options, false);
    }

    public function RequireDev(string $package, ?string $version = '', ?string $options = ''): self
    {
        return $this->requireAny($package, $version, true, $options, false);
    }

    public function RemoveDev(string $package): self
    {
        return $this->Remove($package, true);
    }

    public function Remove(string $package, ?bool $devOnly = false): self
    {
        $devFlag = $devOnly ? '--dev' : '';
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'composer remove ' . $package . ' ' . $devFlag. ' --no-interaction',
            'running composer remove ' . $package . ' ' . $devFlag,
            false
        );

        return $this;
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
            'composer ' . $globalPhrase . ' require ' . $package . $version . ' ' . $devFlag . ' ' . $options. ' --no-interaction',
            'running composer require ' . $package . $version . ' ' . $devFlag . ' ' . $options,
            false
        );

        return $this;
    }
}
