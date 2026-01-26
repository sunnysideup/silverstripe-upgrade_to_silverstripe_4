<?php

namespace Sunnysideup\UpgradeSilverstripe\Traits;

use Sunnysideup\UpgradeSilverstripe\ModuleUpgrader;

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
     * @return self
     */
    protected function setMu(ModuleUpgrader $mu)
    {
        $this->myMu = $mu;

        return $this;
    }

    protected function mu(): ModuleUpgrader
    {
        return $this->myMu;
    }
}
