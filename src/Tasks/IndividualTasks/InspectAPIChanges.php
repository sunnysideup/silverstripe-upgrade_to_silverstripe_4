<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\MetaUpgraderTask;


class InspectAPIChanges extends MetaUpgraderTask
{

    public function upgrade($params = [])
    {
        $codeDir = $this->mo->findCodeDir();
        $this->runSilverstripeUpgradeTask('inspect', $this->mo->getWebRootDir(), $codeDir);
        $this->setCommitMessage('MAJOR: core upgrade to SS4 - STEP 2 (inspect)');
    }

}
