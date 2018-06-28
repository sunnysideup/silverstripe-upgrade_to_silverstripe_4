<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class Upgrade extends Task
{
    public function upgrader($params = [])
    {
        $codeDir = $this->mu->findCodeDir();
        $this->runSilverstripeUpgradeTask('upgrade', $this->mu->getWebRootDirLocation(), $codeDir);
        $this->setCommitMessage('MAJOR: core upgrade to SS4 - STEP 1 (upgrade)');
    }
}
