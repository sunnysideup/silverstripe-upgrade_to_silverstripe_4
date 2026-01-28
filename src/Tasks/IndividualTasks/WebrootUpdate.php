<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * This task runs the silverstripe upgrade task 'webroot' to configure
 * your project to use the public web root structure
 */
class WebrootUpdate extends Task
{
    protected $taskStep = 'SS3->SS4';

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

    public function runActualTask($params = []): ?string
    {
        $this->runSilverstripeUpgradeTask(
            'webroot',
            $this->param1,
            $this->param2,
            $this->rootDirForCommand,
            $this->settings
        );
        $this->setCommitMessage('API:  adding webroot concept');
        return null;
    }

    public function hasCommitAndPush()
    {
        return true;
    }
}
