<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Updates the composer requirements to reflect the new version and package names
 * in the composer file of your module
 */
class RemoveComposerRequirements extends Task
{
    public function getTitle()
    {
        return 'Remove composer.json requirements';
    }

    public function getDescription()
    {
        return '
            Remove requirements in composer.json file for
            '.($this->package ?: 'an Old Package').'
            For example, we remove silverstripe/framework requirement from 3 to 4.';
    }

    protected $package = '';

    public function runActualTask($params = [])
    {
        $package = $this->package;

        $command =
        'if(isset($data["require"]["'.$package.'"])) { '
        .'    unset($data["require"]["'.$package.'"]);'
        .'}';

        $comment = 'remove the requirement for '.$package;

        $this->updateJSONViaCommandLine(
            $this->mu()->getModuleDirLocation(),
            $command,
            $comment
        );

        $this->setCommitMessage('MAJOR: remove composer requirements to SS4 - removing requirements for: '.$this->package);
    }
}
