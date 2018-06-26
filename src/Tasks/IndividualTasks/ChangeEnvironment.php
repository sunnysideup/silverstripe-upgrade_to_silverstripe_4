<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class ChangeEnvironment extends Task
{
    public function upgrader($params = [])
    {
        $this->runSilverstripeUpgradeTask('environment');
        $this->setCommitMessage('MAJOR: changing environment file(s)');
    }
}
