<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Runs the silverstripe/upgrade task "recompose". See:
 * https://github.com/silverstripe/silverstripe-upgrader#recompose'
 */
class RecomposeHomeBrew extends Task
{
    protected $taskStep = 's20';

    public function getTitle()
    {
        return 'Update composer.json to '.$this->mu()->getFrameworkComposerRestraint().'';
    }

    public function getDescription()
    {
        return '
            Updates the requirements in the composer.json file without any extras.' ;
    }

    protected $requireLinesToAdd = [
        'composer/installers' => '^1.6',
        'silverstripe/recipe-cms' => ''
    ];


    public function runActualTask($params = [])
    {
        if(! $this->requireLinesToAdd['silverstripe/recipe-cms']) {
            $this->requireLinesToAdd['silverstripe/recipe-cms'] = $this->mu()->getFrameworkComposerRestraint();
        }
        $command =
        'unset($data["require"]["silverstripe/cms"]);'.
        'unset($data["require"]["silverstripe/recipe-cms"]);'.
        'unset($data["require"]["composer/installers"]);';
        foreach ($this->requireLinesToAdd as $key => $value) {
            $command .=
        '$data["require"]["'.$key.'"] = "'.$value.'"; ';
        };
        $this->updateJSONViaCommandLine(
            $this->mu()->getGitRootDir(),
            $command,
            'exposing javascript, images and css'
        );
        $this->setCommitMessage('MAJOR: upgrading composer requirements to SS4 ');
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
