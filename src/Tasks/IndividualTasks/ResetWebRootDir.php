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
        $this->mu()->execMe(
            $this->mu()->getAboveWebRootDirLocation(),
            'rm ' . $this->mu()->getWebRootDirLocation() . ' -rf',
            'remove the upgrade dir: ' . $this->mu()->getWebRootDirLocation(),
            false
        );
        $fixer = FileSystemFixes::inst($this->mu());
        $fixer->mkDir(
            $this->mu()->getAboveWebRootDirLocation(),
            $this->mu()->getWebRootDirLocation()
        );
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
