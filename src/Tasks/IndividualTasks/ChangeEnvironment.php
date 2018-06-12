<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\MetaUpgraderTask;

class ChangeEnvironment extends MetaUpgraderTask
{
    public function upgrader($params = [])
    {
        $this->runSilverstripeUpgradeTask('environment');
        $this->setCommitMessage('MAJOR: changing environment file(s)');
    }
}
