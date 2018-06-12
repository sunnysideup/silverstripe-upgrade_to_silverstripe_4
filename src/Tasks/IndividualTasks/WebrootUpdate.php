<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\MetaUpgraderTask;

class WebRootUpdate extends MetaUpgraderTask
{
    public function upgrader($params = [])
    {
        $this->runSilverstripeUpgradeTask('webroot');
        $this->setCommitMessage('MAJOR: adding webroot concept');
    }
}
