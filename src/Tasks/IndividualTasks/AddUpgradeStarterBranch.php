<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Tasks\Helpers\Git;
use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class AddUpgradeStarterBranch extends Task
{
    protected $taskStep = 's10';

    public function getTitle()
    {
        return 'Creates a start branch for upgrading';
    }

    public function getDescription()
    {
        return '
            Creates a starter branch: "' . $this->mu()->getNameOfUpgradeStarterBranch() . '" of your module/app
            from the "' . $this->mu()->getNameOfBranchForBaseCode() . '" branch.
            If it does not exist.
            These branch names can be customised with setNameOfUpgradeStarterBranch and setNameOfBranchForBaseCode.
            ';
    }

    /**
     * @param  array  $params not currently used for this task
     */
    public function runActualTask($params = []): ?string
    {
        Git::inst($this->mu())->createNewBranchIfItDoesNotExist(
            $this->mu()->getGitRootDir(),
            $this->mu()->getNameOfUpgradeStarterBranch(),
            $this->mu()->getNameOfBranchForBaseCode()
        );
        return null;
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
