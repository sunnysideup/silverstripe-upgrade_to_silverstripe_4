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

    protected $toExpose = [
        'javascript',
        'images',
        'img',
        'css',
        'fonts',
        'js'
    ];

    public function runActualTask($params = [])
    {
        $expose = [];
        foreach($this->toExpose as $folder) {
            if(file_exists($this->mu()->getModuleDirLocation().'/'.$folder)) {
                $expose[] = $folder;
            }
        }
        if(count($expose)) {
            $command =
            'if(!isset($data["extra"]["expose"])) { '
            .'    $data["extra"]["expose"] = ["'.implode('", "', $expose).'"]; '
            .'}';
            $this->updateJSONViaCommandLine(
                $this->mu()->getModuleDirLocation(),
                $command,
                'exposing javascript, images and css'
            );

            $this->setCommitMessage('MAJOR: exposing folders'.implode(',', $expose));
        }
    }

}
