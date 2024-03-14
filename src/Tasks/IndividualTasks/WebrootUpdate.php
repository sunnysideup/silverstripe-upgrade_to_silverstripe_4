<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task runs the silverstripe upgrade task 'webroot' to configure
 * your project to use the public web root structure
 */
class WebrootUpdate extends Task
{
    protected $param1 = '';

    protected $param2 = '';

    protected $rootDirForCommand = '';

    protected $settings = '';

    public function getTitle()
    {
        return 'Fix Folder Case';
    }

    public function getDescription()
    {
        return '
            Runs the silverstripe/upgrade task "webroot". See:
            https://github.com/silverstripe/silverstripe-upgrader#webroot.
            Configure your project to use the public web root structure
            introduced with SilverStripe 4.1';
    }

    public function runActualTask($params = [])
    {
        $this->runSilverstripeUpgradeTask(
            'webroot',
            $this->param1,
            $this->param2,
            $this->rootDirForCommand,
            $this->settings
        );
        $this->setCommitMessage('API:  adding webroot concept');
    }

    public function hasCommitAndPush()
    {
        true;
    }
}
