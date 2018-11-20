<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Updates the composer requirements to reflect the new version and package names
 * in the composer file of your module
 */
class AddVendorExposeDataToComposer extends Task
{
    public function getTitle()
    {
        return 'Adds vendor expose data to composer';
    }

    public function getDescription()
    {
        return '
            By default we expose all the client related files (images, css and javascript)';
    }

    public function runActualTask($params = [])
    {

        $location = $this->mu()->getModuleDirLocation().'/composer.json';

        $this->mu()->execMe(
            $this->mu()->getModuleDirLocation(),
            'php -r  \''
                .'$jsonString = file_get_contents("'.$location.'"); '
                .'$data = json_decode($jsonString, true); '
                .'if(!isset($data["extra"]["expose"])) { '
                .'    $data["extra"]["expose"] = ["javascript","images","css"]; '
                .'}'
                .'$newJsonString = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); '
                .'file_put_contents("'.$location.'", $newJsonString); '
                .'\'',
            'exposing javascript, images and css in '.$location,
            false
        );
        $this->setCommitMessage('MAJOR: upgrading composer requirements to SS4 - updating core requirements');
    }

}
