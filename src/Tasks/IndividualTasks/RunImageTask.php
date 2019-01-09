<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Run a dev/build as a smoke test to see if all is well
 */
class RunImageTask extends Task
{
    public function getTitle()
    {
        return 'Run dev/tasks/MigrateFileTask';
    }

    public function getDescription()
    {
        return '
            Run a dev/tasks/MigrateFileTask to upgrade files and images.' ;
    }

    public function runActualTask($params = [])
    {
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'php vendor/silverstripe/framework/cli-script.php dev/tasks/MigrateFileTask flush=all',
            'MigrateFileTask is running',
            false
        );
    }

    public function hasCommitAndPush()
    {
        return false;
    }
}
