<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * Runs the silverstripe/upgrade task "environment". See:
 * https://github.com/silverstripe/silverstripe-upgrader#environment.
 * You can use this command to migrate an SilverStripe 3 _ss_environment.php
 * file to the .env format used by SilverStripe 4.'
 */
class DoctorTask extends Task
{
    protected $taskStep = 's30';

    protected $param1 = '';

    protected $param2 = '';

    protected $rootDirForCommand = '';

    public function getTitle()
    {
        return 'Fix up .htaccess and index.html';
    }

    public function getDescription()
    {
        return '
            Runs the silverstripe/upgrade task "doctor". See:
            https://github.com/silverstripe/silverstripe-upgrader#doctor.
            CAREFUL: will remove any customisations!';
    }

    public function runActualTask($params = []): ?string
    {
        if ($this->mu()->getIsModuleUpgrade()) {
            //do nothing ...
        } else {
            $this->runSilverstripeUpgradeTask(
                'doctor',
                $this->param1,
                $this->param2,
                $this->rootDirForCommand
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
