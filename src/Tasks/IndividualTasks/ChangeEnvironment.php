<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class ChangeEnvironment extends Task
{
    public function getTitle()
    {
        return 'Change Environment File';
    }

    public function getDescription()
    {
        return '
            Runs the silverstripe/upgrade task "environment". See:
            https://github.com/silverstripe/silverstripe-upgrader#environment.
            You can use this command to migrate an SilverStripe 3 _ss_environment.php
            file to the .env format used by SilverStripe 4.' ;
    }

    protected $runDir = '';

    protected $param1 = '';

    protected $param2 = '';

    protected $settings = '';

    public function upgrader($params = [])
    {
        $this->runSilverstripeUpgradeTask(
            'environment',
            $this->runDir,
            $this->param1,
            $this->param2,
            $this->settings
        );
        $this->setCommitMessage('MAJOR: changing environment file(s)');
    }
}
