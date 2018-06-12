<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\MetaUpgraderTask;


class UpdateComposerRequirements extends MetaUpgraderTask
{

    public function upgrade($params = [])
    {
        $package = $params['Package'];
        $newVersion = $params['NewVersion'];
        if(isset($params['ReplacementPackage'])) {
            $newPackage = $params['ReplacementPackage'];
        } else {
            $newPackage = $package;
        }
        $location = $this->mo->getModuleDir().'/composer.json';

        $this->mo->execMe(
            $this->mo->getModuleDir(),
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
            'replace in '.$location.' the require for '.$module.' with '.$newVersion,
            false
        );
        $this->setCommitMessage('MAJOR: upgrading composer requirements to SS4 - updating core requirements');
    }

}
