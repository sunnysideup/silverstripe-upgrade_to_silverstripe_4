<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Replaces the composer type from silverstripe-module to silverstripe-vendormodule in line with SS4 standards.
 * This means your module will be installed in the vendor folder after this upgrade.
 */
class UpdateComposerModuleType extends Task
{
    public function getTitle()
    {
        return 'Update composer type to silverstripe-vendormodule ';
    }

    public function getDescription()
    {
        return '
            Replaces the composer type from silverstripe-module to silverstripe-vendormodule in line with SS4 standards.
            This means your module will be installed in the vendor folder after this upgrade.';
    }


    public function runActualTask($params = [])
    {
        $location = $this->mu->getModuleDirLocation().'/composer.json';

        $this->mu->execMe(
            $this->mu->getModuleDirLocation(),
            'php -r  \''
                .'$jsonString = file_get_contents("'.$location.'"); '
                .'$data = json_decode($jsonString, true); '
                .'if(isset($data["type"]) && $data["type"] === "silverstripe-module") { '
                .'    $data["type"] = "silverstripe-vendormodule";'
                .'}'
                .'$newJsonString = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); '
                .'file_put_contents("'.$location.'", $newJsonString); '
                .'\'',
            'Removing extra.installer-name variable from composer.json',
            false
        );
        $this->setCommitMessage('MAJOR: '.$this->getTitle());
    }


}
