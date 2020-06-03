<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers;

use Sunnysideup\UpgradeToSilverstripe4\ModuleUpgrader;

class ComposerJsonFixes
{
    protected static $inst = null;

    protected $myMu = null;

    public static function inst($mu)
    {
        if (self::$inst === null) {
            self::$inst = new ComposerJsonFixes();
            self::$inst->setMu($mu);
        }
        return self::$inst;
    }

    public function getJSON(string $dir)
    {
        $location = $dir . '/composer.json';
        $jsonString = file_get_contents($location);

        return json_decode($jsonString, true);
    }

    public function setJSON(string $dir, array $data)
    {
        $location = $dir . '/composer.json';
        $newJsonString = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents("'.${location}.'", $newJsonString);
        return $this;
    }

    public function UpdateJSONViaCommandLine(string $dir, string $code, string $comment)
    {
        $location = $dir . '/composer.json';
        $this->mu()->execMe(
            $dir,
            'php -r  \''
                . '$jsonString = file_get_contents("' . $location . '"); '
                . '$data = json_decode($jsonString, true); '
                . $code
                . '$newJsonString = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); '
                . 'file_put_contents("' . $location . '", $newJsonString); '
                . '\'',
            $comment . ' --- in ' . $location,
            false
        );
    }

    /**
     * @param ModuleUpgrader $mu
     * @return ComposerJsonFixes
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
