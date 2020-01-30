<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

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

    public function runActualTask($params = [])
    {
        $this->mu()->execMe(
            $this->mu()->getGitRootDir(),
            'git fetch --all ',
            'get the lastest git data',
            false
        );
        $this->mu()->execMe(
            $this->mu()->getGitRootDir(),
            'if git show-ref --quiet refs/heads/' . $this->mu()->getNameOfTempBranch() . '; then git branch -d ' . $this->mu()->getNameOfTempBranch() . '; git push origin --delete ' . $this->mu()->getNameOfTempBranch() . '; fi',
            'delete upgrade branch (' . $this->mu()->getNameOfTempBranch() . ') locally',
            false
        );

        $this->mu()->execMe(
            $this->mu()->getGitRootDir(),
            'git push origin --delete ' . $this->mu()->getNameOfTempBranch(),
            'delete upgrade branch (' . $this->mu()->getNameOfTempBranch() . ') remotely',
            false
        );

        $this->mu()->execMe(
            $this->mu()->getGitRootDir(),
            'git checkout ' . $this->mu()->getNameOfUpgradeStarterBranch(),
            'check out : ' . $this->mu()->getNameOfUpgradeStarterBranch() . ' as a starting point',
            false
        );

        $this->mu()->execMe(
            $this->mu()->getGitRootDir(),
            'git pull origin ' . $this->mu()->getNameOfUpgradeStarterBranch(),
            'get the latest details for : ' . $this->mu()->getNameOfUpgradeStarterBranch() . '',
            false
        );

        $this->mu()->execMe(
            $this->mu()->getGitRootDir(),
            'git checkout -b ' . $this->mu()->getNameOfTempBranch() . ' ' . $this->mu()->getNameOfUpgradeStarterBranch(),
            'create and checkout new branch: ' . $this->mu()->getNameOfTempBranch(),
            false
        );
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
