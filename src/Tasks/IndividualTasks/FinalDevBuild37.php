<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

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

    public function runActualTask($params = [])
    {
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'framework/sake dev/build flush=all',
            'It is time for a dev/build',
            false
        );
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
