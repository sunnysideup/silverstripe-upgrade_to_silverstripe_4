<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Runs the silverstripe/upgrade task "environment". See:
 * https://github.com/silverstripe/silverstripe-runActualTask#environment.
 * You can use this command to migrate an SilverStripe 3 _ss_environment.php
 * file to the .env format used by SilverStripe 4.'
 */
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
            https://github.com/silverstripe/silverstripe-runActualTask#environment.
            You can use this command to migrate a SilverStripe 3 _ss_environment.php
            file to the Silverstripe 4 .env format.' ;
    }

    protected $runDir = '';

    protected $param1 = '';

    protected $param2 = '';

    protected $settings = '';

    public function runActualTask($params = [])
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
