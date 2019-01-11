<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Run a dev/build as a smoke test to see if all is well
 */
class DoMigrateSiteTreeLinkingTask extends Task
{
    public function getTitle()
    {
        return 'Run dev/tasks/MigrateSiteTreeLinkingTask';
    }

    public function getDescription()
    {
        return '
            Run a dev/tasks/MigrateSiteTreeLinkingTask to upgrade sitetree links.' ;
    }

    public function runActualTask($params = [])
    {
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'php vendor/silverstripe/framework/cli-script.php dev/tasks/MigrateSiteTreeLinkingTask flush=all',
            'MigrateSiteTreeLinkingTask is running',
            false
        );
    }

    public function hasCommitAndPush()
    {
        return false;
    }
}
