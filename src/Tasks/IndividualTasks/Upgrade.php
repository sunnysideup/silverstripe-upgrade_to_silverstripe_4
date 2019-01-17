<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Runs the silverstripe upgrade task 'upgrade'.
 * More information on this task at https://github.com/silverstripe/silverstripe-upgrader#upgrade
 */
class Upgrade extends Task
{
    protected $taskStep = 's40';

    public function getTitle()
    {
        return 'Update Code';
    }


    public function getDescription()
    {
        return '
            Runs the silverstripe/upgrade task "upgrade". See:
            Upgrade a variety of stuff (e.g. update reference with namespaces)
            https://github.com/silverstripe/silverstripe-upgrader#upgrade' ;
    }

    protected $runDir = '';

    protected $param1 = '';

    protected $param2 = '';

    protected $settings = '';

    public function runActualTask($params = [])
    {
        if (empty($this->runDir)) {
            $this->runDir = $this->mu()->getWebRootDirLocation();
        }
        if (empty($this->param1)) {
            $this->param1 = $this->mu()->findCodeDir();
        }
        $this->runSilverstripeUpgradeTask(
            'upgrade',
            $this->runDir,
            $this->param1,
            $this->param2,
            $this->settings
        );
        $this->setCommitMessage('MAJOR: core upgrade to SS4 - STEP 1 (upgrade)');
    }
    
    protected function hasCommitAndPush()
    {
        return true;
    }
}
