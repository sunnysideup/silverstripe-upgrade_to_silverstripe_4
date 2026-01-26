<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * Run a dev/build as a smoke test to see if all is well
 */
class FinalDevBuild37 extends Task
{
    protected $taskStep = 's60';

    public function getTitle()
    {
        return 'Run dev/build';
    }

    public function getDescription()
    {
        return '
            Run a dev/build as a smoke test to see if all is well.';
    }

    public function runActualTask($params = []): ?string
    {
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'framework/sake dev/build flush=all',
            'It is time for a dev/build',
            false
        );
        return null;
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
