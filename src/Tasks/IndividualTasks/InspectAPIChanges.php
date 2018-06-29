<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class InspectAPIChanges extends Task
{
    public function upgrader($params = [])
    {
        $codeDir = $this->mu->findCodeDir();

        $this->mu->execMe(
            $this->mu->getWebRootDirLocation(),
            'composer dump-autoload',
            'run composer dump-autoload',
            false
        );

        $this->runSilverstripeUpgradeTask('inspect', $this->mu->getWebRootDirLocation(), $codeDir);
        $this->setCommitMessage('MAJOR: core upgrade to SS4: INSPECT');
    }
}
