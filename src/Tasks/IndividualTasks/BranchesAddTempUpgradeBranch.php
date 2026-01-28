<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Tasks\Helpers\Git;
use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * Adds a new branch to your repository that is going to be used for upgrading it.
 */
class BranchesAddTempUpgradeBranch extends Task
{
    protected $taskStep = 'ANY';

    public function getTitle()
    {
        return 'Add Upgrade Branch';
    }

    public function getDescription()
    {
        return '
            Adds a new branch ' . $this->mu()->getNameOfTempBranch() . '
            based on ' . $this->mu()->getNameOfUpgradeStarterBranch() . '
            (both names can be customised)
            to your repository (' . ($this->mu()->getVendorName() ?: 'ErrorInVendorName') . '/' . ($this->mu()->getPackageName() ?: 'ErrorInPackageName') . ')
            that is going to be used for upgrading it.
            If it already exists then it will first be DELETED!';
    }

    public function runActualTask($params = []): ?string
    {
        Git::inst($this->mu())
            ->deleteBranch(
                $this->mu()->getGitRootDir(),
                $this->mu()->getNameOfTempBranch()
            )
            ->createNewBranch(
                $this->mu()->getGitRootDir(),
                $this->mu()->getNameOfTempBranch(),
                $this->mu()->getNameOfUpgradeStarterBranch()
            );
        return null;
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
