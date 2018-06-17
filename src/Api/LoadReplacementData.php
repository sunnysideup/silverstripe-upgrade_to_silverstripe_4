<?php

/**
 * loads yml data if strings to replace in
 * code
 */

namespace Sunnysideup\UpgradeToSilverstripe4\Api;

use SilverStripe\Upgrader\Util\ConfigFile;


class LoadReplacementData
{


    /**
     * Module Object
     * @var MetaUpgrader
     */
    protected $mo = null;

    public function __construct($mo, $params = [])
    {
        $this->params = $params;
        $this->mo = $mo;
    }

    public function getReplacementArrays()
    {
        $array = ConfigFile::loadConfig();

        return $array;
    }

    public function getTos()
    {

    }

}
