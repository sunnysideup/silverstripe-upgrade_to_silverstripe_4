<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Updates the composer requirements to reflect the new version and package names
 * in the composer file of your module
 */
class RemoveComposerRequirements extends Task
{
    protected $taskStep = 's20';

    protected $package = '';

    public function getTitle()
    {
        return 'Remove composer.json requirements';
    }

    public function getDescription()
    {
        return '
            Remove requirements in composer.json file for
            ' . ($this->package ?: 'an Old Package') . '
            For example, we remove silverstripe/framework requirement from 3 to 4.';
    }

    public function runActualTask($params = [])
    {
        $package = $this->package;

        $command = 'unset($data["require"]["' . $package . '"]);';

        $comment = 'remove the requirement for ' . $package . ' from ' . $this->mu()->getGitRootDir();

        $this->updateJSONViaCommandLine(
            $this->mu()->getGitRootDir(),
            $command,
            $comment
        );

        $this->setCommitMessage('MAJOR: remove composer requirements - removing requirements for: ' . $this->package);
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
