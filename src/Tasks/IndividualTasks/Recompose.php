<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\MetaUpgraderTask;

class Recompose extends MetaUpgraderTask
{
    public function upgrader($params = [])
    {
        $this->runSilverstripeUpgradeTask('recompose', $this->mo->getModuleDir());
        $this->setCommitMessage('MAJOR: upgrading composer requirements to SS4 - STEP 2');
    }
}
