<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * Runs the silverstripe/upgrade task "reorganise". See:
 * https://github.com/silverstripe/silverstripe-upgrader#reorganise
 * You can use this command to reorganise your folder structure to
 * conform to the new structure introduced with SilverStripe 4.1.
 * Your mysite folder will be renamed to app and your code folder will be rename to src.
 */
class Reorganise extends Task
{
    protected $taskStep = 's50';

    protected $param1 = '';

    protected $param2 = '';

    protected $rootDirForCommand = '';

    protected $settings = '';

    public function getTitle()
    {
        return 'move mysite/code folder to app/src';
    }

    public function getDescription()
    {
        return '
            Runs the silverstripe/upgrade task "reorganise". See:
            https://github.com/silverstripe/silverstripe-upgrader#reorganise
            You can use this command to reorganise your folder structure to
            conform to the new structure introduced with SilverStripe 4.1.
            Your mysite folder will be renamed to app and your code folder will be renamed to src.
            ';
    }

    public function runActualTask($params = []): ?string
    {
        if ($this->mu()->getIsModuleUpgrade()) {
        } else {
            $this->runSilverstripeUpgradeTask(
                'reorganise',
                $this->param1,
                $this->param2,
                $this->rootDirForCommand,
                $this->settings
            );
            $this->setCommitMessage('API:  re-organising files');
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
