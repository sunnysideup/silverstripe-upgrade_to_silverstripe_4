<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class FinalDevBuild extends Task
{

    public function getTitle()
    {
        return 'Run dev/build';
    }

    public function getDescription()
    {
        return '
            Run a dev/build as a smoke test to see if all is well.' ;
    }

    public function upgrader($params = [])
    {
        $this->mu->execMe(
            $this->mu->getWebRootDirLocation(),
            'php vendor/silverstripe/framework/cli-script.php dev/build flush=all',
            'It is time for a dev/build',
            false
        );
    }

    public function hasCommit()
    {
        return false;
    }
}
