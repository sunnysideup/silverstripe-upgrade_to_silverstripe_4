<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Tasks\Helpers\Git;
use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class AddLegacyBranch extends Task
{
    protected $taskStep = 'ANY';

    /**
     * @var string what should the legacy branch be called
     */
    protected $nameOfLegacyBranch = '3';

    public function getTitle()
    {
        return 'Add Legacy Branch';
    }

    public function getDescription()
    {
        return '
            Creates a legacy branch: "' . $this->nameOfLegacyBranch . '" of your module
            from the "' . $this->mu()->getNameOfBranchForBaseCode() . '" branch so that you
            can keep making bugfixes to older versions.';
    }

    /**
     * @param  array  $params not currently used for this task
     */
    public function runActualTask($params = []): ?string
    {
        $gitRootDir = $this->mu()->getGitRootDir();
        Git::inst($this->mu())->createNewBranchIfItDoesNotExist(
            $gitRootDir,
            $this->nameOfLegacyBranch,
            $this->mu()->getNameOfBranchForBaseCode()
        );
        return null;
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
