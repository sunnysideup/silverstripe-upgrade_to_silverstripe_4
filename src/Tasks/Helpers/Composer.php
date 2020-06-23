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

    public function DumpAutoload(string $dir)
    {
        $this->mu()->execMe(
            $dir,
            'composer dumpautoload',
            'run composer dumpautoload',
            false
        );
    }

    public function Require(string $package, ?string $version = '', ?bool $devOnly = false, ? string $options = '')
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
