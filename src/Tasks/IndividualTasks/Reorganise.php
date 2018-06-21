<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\MetaUpgraderTask;

class Reorganise extends MetaUpgraderTask
{
    public function upgrader($params = [])
    {
        $this->runSilverstripeUpgradeTask('reorganise');
        $this->setCommitMessage('MAJOR: re-organising files');
    }
}