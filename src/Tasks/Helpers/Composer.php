<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers;

use Sunnysideup\UpgradeToSilverstripe4\ModuleUpgrader;

class Composer
{
    protected static $inst = null;

    protected $myMu = null;

    protected $defaultOptions = '';

    public static function inst($mu)
    {
        if (self::$inst === null) {
            self::$inst = new Composer();
            self::$inst->setMu($mu);
        }
        return self::$inst;
    }

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
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'composer require ' . $package . $version . ' ' . $devFlag . ' ' . $options,
            'running composer require ' . $package . $version . ' ' . $devFlag . ' ' . $options,
            false
        );

        return $this;
    }

    /**
     * @param ModuleUpgrader $mu
     * @return Composer
     */
    protected function setMu(ModuleUpgrader $mu)
    {
        $this->myMu = $mu;

        return $this;
    }

    /**
     * @return ModuleUpgrader
     */
    protected function mu()
    {
        return $this->myMu;
    }
}
