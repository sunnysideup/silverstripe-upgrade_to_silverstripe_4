<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * Run a dev/build as a smoke test to see if all is well
 */
class DoMigrateSiteTreeLinkingTask extends Task
{
    protected $taskStep = 's60';

    public function getTitle()
    {
        return 'Run dev/tasks/MigrateSiteTreeLinkingTask';
    }

    public function getDescription()
    {
        return '
            Run a dev/tasks/MigrateSiteTreeLinkingTask to upgrade sitetree links.';
    }

    /**
     * @param array $params
     */
    public function runActualTask($params = []): ?string
    {
        if ($this->mu()->getIsModuleUpgrade()) {
            return null;
        }
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'php vendor/silverstripe/framework/cli-script.php dev/tasks/MigrateSiteTreeLinkingTask flush=all',
            'MigrateSiteTreeLinkingTask is running',
            false
        );
        return null;
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
