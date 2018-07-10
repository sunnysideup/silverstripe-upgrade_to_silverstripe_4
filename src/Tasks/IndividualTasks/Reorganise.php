<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class Reorganise extends Task
{

    public function getTitle()
    {
        return 'move mysite/code folder to app/src';
    }

    public function getDescription()
    {
        return '
            Runs the silverstripe/upgrade task "reorganise". See:
            https://github.com/silverstripe/silverstripe-runActualTask#reorganise
            You can use this command to reorganise your folder structure to
            conform to the new structure introduced with SilverStripe 4.1.
            Your mysite folder will be renamed to app and your code folder will be rename to src.
            ' ;
    }

    protected $runDir = '';

    protected $param1 = '';

    protected $param2 = '';

    protected $settings = '';

    public function runActualTask($params = [])
    {
        $this->runSilverstripeUpgradeTask(
            'reorganise',
            $this->runDir,
            $this->param1,
            $this->param2,
            $this->settings
        );
        $this->setCommitMessage('MAJOR: re-organising files');
    }
}
