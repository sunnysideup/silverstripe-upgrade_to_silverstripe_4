<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Api\FileSystemFixes;
use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * Delete the web root directory to allow for a fresh install.
 */
class ResetWebRootDir extends Task
{
    protected $taskStep = 'ANY';

    public function getTitle()
    {
        return 'Remove and reset the web root';
    }

    public function getDescription()
    {
        return '
            Delete the web root directory to allow for a fresh install.';
    }

    public function runActualTask($params = []): ?string
    {
        FileSystemFixes::inst($this->mu())
            ->removeDirOrFile($this->mu()->getWebRootDirLocation(), $this->mu()->getAboveWebRootDirLocation());
        FileSystemFixes::inst($this->mu())
            ->mkDir($this->mu()->getWebRootDirLocation(), $this->mu()->getAboveWebRootDirLocation());
        return null;
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
