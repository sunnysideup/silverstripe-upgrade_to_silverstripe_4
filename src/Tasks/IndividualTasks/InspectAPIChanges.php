<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class InspectAPIChanges extends Task
{
    public function upgrader($params = [])
    {
        $codeDir = $this->mo->findCodeDir();

        $this->mo->execMe(
            $this->mo->getWebRootDirLocation(),
            'composer dump-autoload',
            'update composer autoload',
            false
        );

        $this->runSilverstripeUpgradeTask('inspect', $this->mo->getWebRootDirLocation(), $codeDir);
        $this->setCommitMessage('MAJOR: core upgrade to SS4 - STEP 2 (inspect)');
    }
}
