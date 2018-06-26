<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class Upgrade extends Task
{
    public function upgrader($params = [])
    {
        $codeDir = $this->mo->findCodeDir();
        $this->runSilverstripeUpgradeTask('upgrade', $this->mo->getWebRootDirLocation(), $codeDir);
        $this->setCommitMessage('MAJOR: core upgrade to SS4 - STEP 1 (upgrade)');
    }
}
