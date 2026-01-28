<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * Runs the silverstripe/upgrade task "environment". See:
 * https://github.com/silverstripe/silverstripe-upgrader#environment.
 * You can use this command to migrate an SilverStripe 3 _ss_environment.php
 * file to the .env format used by SilverStripe 4.'
 */
class ChangeEnvironment extends Task
{
    protected $taskStep = 'SS3->SS4';

    protected $rootDirForCommand = '';

    protected $param1 = '';

    protected $param2 = '';

    protected $settings = '';

    public function getTitle()
    {
        return 'Change Environment File';
    }

    public function getDescription()
    {
        return '
            Runs the silverstripe/upgrade task "environment". See:
            https://github.com/silverstripe/silverstripe-upgrader#environment.
            You can use this command to migrate a SilverStripe 3 _ss_environment.php
            file to the Silverstripe 4 .env format.';
    }

    public function runActualTask($params = []): ?string
    {
        if ($this->mu()->getIsModuleUpgrade()) {
            //do nothing
        } else {
            $this->runSilverstripeUpgradeTask(
                'environment',
                $this->param1,
                $this->param2,
                $this->rootDirForCommand,
                $this->settings
            );
            $this->setCommitMessage('API:  changing environment file(s)');
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
