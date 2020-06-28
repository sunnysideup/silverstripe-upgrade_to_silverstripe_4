<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\FileSystemFixes;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Delete the web root directory to allow for a fresh install.
 */
class ResetWebRootDir extends Task
{
    protected $taskStep = 's00';

    public function getTitle()
    {
        return 'Remove and reset the web root';
    }

    public function getDescription()
    {
        return '
            Delete the web root directory to allow for a fresh install.';
    }

    public function runActualTask($params = [])
    {
        FileSystemFixes::inst($this->mu())
            ->removeDirOrFile($this->mu()->getWebRootDirLocation(), $this->mu()->getAboveWebRootDirLocation());
        FileSystemFixes::inst($this->mu())
            ->mkDir($this->mu()->getWebRootDirLocation(), $this->mu()->getAboveWebRootDirLocation());
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
