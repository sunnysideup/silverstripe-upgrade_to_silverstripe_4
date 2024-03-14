<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\Git;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Adds a new branch to your repository that is going to be used for upgrading it.
 */
class AddTempUpgradeBranch extends Task
{
    protected $taskStep = 's10';

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
            to your repository (' . ($this->mu()->getVendorName() ?: 'Vendor Name') . '/' . ($this->mu()->getPackageName() ?: 'Package Name') . ')
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
