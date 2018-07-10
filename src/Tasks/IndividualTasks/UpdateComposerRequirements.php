<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class UpdateComposerRequirements extends Task
{

    public function getTitle()
    {
        return 'Update composer.json requirements';
    }

    public function getDescription()
    {
        return '
            Change '.$this->package.' to '.$this->getReplacementPackage().':'.$this->newVersion.'
            in the composer file of your module.';
    }

    protected $package = '';

    protected $newVersion = '';

    protected $replacementPackage = '';

    public function runActualTask($params = [])
    {
        $package = $this->package;

        $newVersion = $this->newVersion;

        $newPackage = $this->getReplacementPackage();

        $location = $this->mu->getModuleDirLocation().'/composer.json';

        $this->mu->execMe(
            $this->mu->getModuleDirLocation(),
            'php -r  \''
                .'$jsonString = file_get_contents("'.$location.'"); '
                .'$data = json_decode($jsonString, true); '
                .'if(isset($data["require"]["'.$package.'"])) { '
                .'    unset($data["require"]["'.$package.'"]);'
                .'    $data["require"]["'.$newPackage.'"] = "'.$newVersion.'"; '
                .'}'
                .'$newJsonString = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); '
                .'file_put_contents("'.$location.'", $newJsonString); '
                .'\'',
            'replace in '.$location.' the require for '.$package.' with '.$newPackage.':'.$newVersion,
            false
        );
        $this->setCommitMessage('MAJOR: upgrading composer requirements to SS4 - updating core requirements');
    }


    public function getReplacementPackage()
    {
        if (empty($this->replacementPackage)) {
            $newPackage = $this->package;
        } else {
            $newPackage = $this->replacementPackage;
        }

        return $newPackage;
    }

}
