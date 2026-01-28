<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks\ThreeToFour;

use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * Runs the silverstripe/upgrade task "recompose". See:
 * https://github.com/silverstripe/silverstripe-upgrader#recompose'
 */
class Recompose extends Task
{
    protected $taskStep = 'SS3->SS4';

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

    public function runActualTask($params = []): ?string
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
            $this->setCommitMessage('API:  upgrading composer requirements to SS4 - STEP 2');
        }
        return null;
    }

    protected function hasCommitAndPush()
    {
        if ($this->mu()->getIsModuleUpgrade()) {
            return false;
        }
        return true;
    }
}
