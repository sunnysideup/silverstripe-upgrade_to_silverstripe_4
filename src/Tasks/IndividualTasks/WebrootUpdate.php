<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class WebRootUpdate extends Task
{
    public function upgrader($params = [])
    {
        $this->runSilverstripeUpgradeTask('webroot');
        $this->setCommitMessage('MAJOR: adding webroot concept');
    }
}
