<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class Reorganise extends Task
{
    public function upgrader($params = [])
    {
        $this->runSilverstripeUpgradeTask('reorganise');
        $this->setCommitMessage('MAJOR: re-organising files');
    }
}
