<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

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
            ' ;
    }

    protected $param1 = '';

    protected $param2 = '';

    protected $rootDirForCommand = '';

    protected $settings = '';

    public function runActualTask($params = [])
    {
        if($this->mu()->getIsModuleUpgrade()) {

        } else {
            $this->runSilverstripeUpgradeTask(
                'reorganise',
                $this->param1,
                $this->param2,
                $this->rootDirForCommand,
                $this->settings
            );
            $this->setCommitMessage('MAJOR: re-organising files');
        }
    }

    protected function hasCommitAndPush()
    {
        if($this->mu()->getIsModuleUpgrade()) {
            return false;
        }

        return true;
    }
}
