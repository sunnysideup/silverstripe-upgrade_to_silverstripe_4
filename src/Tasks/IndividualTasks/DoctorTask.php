<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Runs the silverstripe/upgrade task "environment". See:
 * https://github.com/silverstripe/silverstripe-upgrader#environment.
 * You can use this command to migrate an SilverStripe 3 _ss_environment.php
 * file to the .env format used by SilverStripe 4.'
 */
class DoctorTask extends Task
{
    protected $taskStep = 's30';

    public function getTitle()
    {
        return 'Fix up .htaccess and index.html';
    }

    public function getDescription()
    {
        return '
            Runs the silverstripe/upgrade task "doctor". See:
            https://github.com/silverstripe/silverstripe-upgrader#doctor.
            CAREFUL: will remove any customisations!' ;
    }

    protected $runDir = '';

    public function runActualTask($params = [])
    {
        if($this->getIsModuleUpgrade()) {
            //do nothing ...
        } else {
            $this->runSilverstripeUpgradeTask(
                'doctor',
                $rootDir = $this->mu()->getWebRootDirLocation()
            );
            $this->setCommitMessage('MAJOR: changing environment file(s)');
        }
    }

    protected function hasCommitAndPush()
    {
        if($this->getIsModuleUpgrade()) {
            return false;
        } else {
            return true;
        }
    }
}
