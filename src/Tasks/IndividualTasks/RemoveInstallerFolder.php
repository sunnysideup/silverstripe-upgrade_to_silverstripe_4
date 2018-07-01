<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class RemoveInstallerFolder extends Task
{

    public function getTitle()
    {
        return 'Remove installer-name from composer.json';
    }

    public function getDescription()
    {
        return '
            Remove installer folder from composer.json file so that package
            installs into vendor folder.' ;
    }

    protected $package = '';

    protected $newVersion = '';

    protected $newPackage = '';

    public function upgrader($params = [])
    {
        $location = $this->mu->getModuleDirLocation().'/composer.json';

        $this->mu->execMe(
            $this->mu->getModuleDirLocation(),
            'php -r  \''
                .'$jsonString = file_get_contents("'.$location.'"); '
                .'$data = json_decode($jsonString, true); '
                .'if(isset($data["extra"]["installer-name"])) { '
                .'    unset($data["extra"]["installer-name"]);'
                .'}'
                .'$newJsonString = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); '
                .'file_put_contents("'.$location.'", $newJsonString); '
                .'\'',
            'Removing extra.installer-name variable from composer.json',
            false
        );
        $this->setCommitMessage('MAJOR: Removing extra.installer-name variable from composer.json ');
    }
}
