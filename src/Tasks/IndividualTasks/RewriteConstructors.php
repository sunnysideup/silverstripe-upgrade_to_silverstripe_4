<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Delete the web root directory to allow for a fresh install.
 */
class RewriteConstructors extends Task
{
    protected $taskStep = 's00';

    public function getTitle()
    {
        return 'Constructor rewrite';
    }

    public function getDescription()
    {
        return '
            rewrites deprecated constructors to be compatible with PHP7.';
    }

    public function runActualTask($params = []): ?string
    {
        $this->mu()->execMe(
            $this->mu()->getAboveWebRootDirLocation(),
            'php ' . $this->mu()->getWebRootDirLocation() . '--disable-class-file-create',
            'create upgrade directory: ' . $this->mu()->getWebRootDirLocation(),
            false
        );
        return null;
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
