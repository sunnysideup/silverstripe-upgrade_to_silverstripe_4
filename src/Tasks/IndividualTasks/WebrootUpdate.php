<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task runs the silverstripe upgrade task 'webroot' to configure
 * your project to use the public web root structure
 */
class WebRootUpdate extends Task
{
    public function getTitle()
    {
        return 'Fix Folder Case';
    }


    public function getDescription()
    {
        return '
            Runs the silverstripe/upgrade task "webroot". See:
            https://github.com/silverstripe/silverstripe-runActualTask#webroot.
            Configure your project to use the public web root structure
            introduced with SilverStripe 4.1' ;
    }

    protected $runDir = '';

    protected $param1 = '';

    protected $param2 = '';

    protected $settings = '';

    public function runActualTask($params = [])
    {
        $this->runSilverstripeUpgradeTask(
            'webroot',
            $this->runDir,
            $this->param1,
            $this->param2,
            $this->settings
        );
        $this->setCommitMessage('MAJOR: adding webroot concept');
    }
}
