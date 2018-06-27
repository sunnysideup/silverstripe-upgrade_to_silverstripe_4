<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class Recompose extends Task
{
    public function upgrader($params = [])
    {
        $this->runSilverstripeUpgradeTask('recompose', $this->mo->getModuleDirLocation());
        $this->setCommitMessage('MAJOR: upgrading composer requirements to SS4 - STEP 2');
    }
}
