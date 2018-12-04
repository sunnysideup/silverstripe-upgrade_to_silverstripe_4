<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Runs the silverstripe/upgrade task "recompose". See:
 * https://github.com/silverstripe/silverstripe-runActualTask#recompose'
 */
class RecomposeHomeBrew extends Task
{
    public function getTitle()
    {
        return 'Update composer.json from 3 to 4 without any extras';
    }

    public function getDescription()
    {
        return '
            Updates the requirements in the composer.json file.' ;
    }

    protected $requireLinesToAdd = [
        'composer/installers' => '^1.6',
        'silverstripe/recipe-cms' => '^4.2'
    ];


    public function runActualTask($params = [])
    {
        $command =
        'unset($data["require"]["silverstripe/cms"]);'.
        'unset($data["require"]["silverstripe/recipe-cms"]);'.
        'unset($data["require"]["composer/installers"]);';
        foreach($this->requireLinesToAdd as $key => $value) {
            $command .=
        '$data["require"]["'.$key.'"] = "'.$value.'"; ';
        };
        $this->updateJSONViaCommandLine(
            $this->mu()->getModuleDirLocation(),
            $command,
            'exposing javascript, images and css'
        );
        $this->setCommitMessage('MAJOR: upgrading composer requirements to SS4 ');
    }
}
