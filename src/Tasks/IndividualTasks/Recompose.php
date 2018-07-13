<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class Recompose extends Task
{
    public function getTitle()
    {
        return 'Update composer.json from 3 to 4';
    }

    public function getDescription()
    {
        return '
            Runs the silverstripe/upgrade task "recompose". See:
            https://github.com/silverstripe/silverstripe-upgrader#recompose' ;
    }

    protected $runDir = '';

    protected $param1 = '';

    protected $param2 = '';

    protected $settings = '';


    public function upgrader($params = [])
    {
        if (empty($this->runDir)) {
            $this->runDir = $this->mu->getModuleDirLocation();
        }
        $this->runSilverstripeUpgradeTask(
            'recompose',
            $this->runDir,
            $this->param1,
            $this->param2,
            $this->settings
        );
        $this->setCommitMessage('MAJOR: upgrading composer requirements to SS4 - STEP 2');
    }
}
