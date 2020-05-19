<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers;

class Composer
{
    protected static $inst = null;

    protected $myMu = null;

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
}
