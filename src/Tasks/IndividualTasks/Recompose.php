<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Runs the silverstripe/upgrade task "recompose". See:
 * https://github.com/silverstripe/silverstripe-upgrader#recompose'
 */
class Recompose extends Task
{
    protected $taskStep = 's20';

    protected $param1 = '';

    protected $param2 = '';

    protected $rootDirForCommand = '';

    protected $settings = '';

    public function getTitle()
    {
        return 'Update composer.json from 3 to 4';
    }

    public function getDescription()
    {
        return '
            Runs the silverstripe/upgrade task "recompose". See:
            https://github.com/silverstripe/silverstripe-upgrader#recompose';
    }

    public function runActualTask($params = [])
    {
        if ($this->mu()->getIsModuleUpgrade()) {
        } else {
            if (! $this->param1) {
                $this->param1 = '--recipe-core-constraint="' . $this->mu()->getFrameworkComposerRestraint() . '"';
            }
            if (empty($this->rootDirForCommand)) {
                $this->rootDirForCommand = $this->mu()->getGitRootDir();
            }
            $this->runSilverstripeUpgradeTask(
                'recompose',
                $this->param1,
                $this->param2,
                $this->rootDirForCommand,
                $this->settings
            );
            $this->setCommitMessage('MAJOR: upgrading composer requirements to SS4 - STEP 2');
        }
    }

    protected function hasCommitAndPush()
    {
        if ($this->mu()->getIsModuleUpgrade()) {
            return false;
        }
        return true;
    }
}
