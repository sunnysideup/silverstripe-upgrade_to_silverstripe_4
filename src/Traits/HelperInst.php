<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Traits;

use Sunnysideup\UpgradeToSilverstripe4\ModuleUpgrader;

trait HelperInst
{
    protected static $inst = null;

    protected $myMu = null;

    public static function inst($mu)
    {
        if (self::$inst === null) {
            $className = static::class;
            self::$inst = new $className();
            self::$inst->setMu($mu);
        }
        return self::$inst;
    }

    /**
     * @param \Sunnysideup\UpgradeToSilverstripe4\ModuleUpgrader $mu
     * @return self
     */
    protected function setMu(ModuleUpgrader $mu)
    {
        $this->myMu = $mu;

        return $this;
    }

    /**
     * @return \Sunnysideup\UpgradeToSilverstripe4\ModuleUpgrader
     */
    protected function mu(): ModuleUpgrader
    {
        return $this->myMu;
    }
}
