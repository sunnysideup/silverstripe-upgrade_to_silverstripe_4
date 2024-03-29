<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\Git;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Adds a new branch to your repository that is going to be used for upgrading it.
 */
class FinaliseUpgradeWithMergeIntoMaster extends Task
{
    protected $taskStep = 's70';

    public function getTitle()
    {
        if ($this->mu()->getRunIrreversibly()) {
            return 'Finalise Upgrade with merge into ' . $this->mu()->getNameOfBranchForBaseCode() . '.';
        }
        return 'Finalise Upgrade with merge into ' . $this->mu()->getNameOfBranchForBaseCode() . ' (TURNED OFF)';
    }

    public function getDescription()
    {
        if ($this->mu()->getRunIrreversibly()) {
            return '
                Merge ' . $this->mu()->getNameOfTempBranch() . ' into ' . $this->mu()->getNameOfBranchForBaseCode() . ' (branch name can be customised).
                CAREFUL - THIS STEP CAN NOT BE UNDONE. IT IS NOT RECOMMENDED!
                You should only run this upgrade task if you are 100% sure.
                You can turn off this task by setting `runIrreversibly` to false in the upgrader.
                You can do this as follows: `$upgrader->setRunIrreversibly(false)`
                 ';
        }
        return '
                Merge ' . $this->mu()->getNameOfTempBranch() . ' (branch name can be customised)
                into ' . $this->mu()->getNameOfBranchForBaseCode() . ' (branch name can be customised).
                This is currently turned off allowing you to run the upgrader more than once without any consequences.
                You can turn on this task by setting `runIrreversibly` to true in the upgrader.
                You can do this as follows: `$upgrader->setRunIrreversibly(true)`.';
    }

    public function runActualTask($params = []): ?string
    {
        if ($this->mu()->getRunIrreversibly()) {
            $this->mu()->setBreakOnAllErrors(true);
            Git::inst($this->mu())
                ->Merge(
                    $this->mu()->getGitRootDir(),
                    $this->mu()->getNameOfTempBranch(),
                    $this->mu()->getNameOfBranchForBaseCode()
                )
                ->deleteBranch(
                    $this->mu()->getGitRootDir(),
                    $this->mu()->getNameOfTempBranch()
                )
                ->deleteBranch(
                    $this->mu()->getGitRootDir(),
                    $this->mu()->getNameOfUpgradeStarterBranch()
                );
            $this->mu()->setBreakOnAllErrors(false);
        }
        return null;
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
