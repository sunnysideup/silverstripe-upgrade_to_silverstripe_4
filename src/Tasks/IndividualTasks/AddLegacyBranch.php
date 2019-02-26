<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class AddLegacyBranch extends Task
{
    protected $taskStep = 's10';

    public function getTitle()
    {
        return 'Add Legacy Branch';
    }

    public function getDescription()
    {
        return '
            Creates a legacy branch: "'.$this->nameOfLegacyBranch.'" of your module so that you
            can keep making bugfixes to older versions.
            You can set the name of the legacy branch as you see fit.';
    }

    /**
     * @var string what should the legacy branch be called
     */
    protected $nameOfLegacyBranch = '3';

    /**
     *
     * @param  array  $params not currently used for this task
     */
    public function runActualTask($params = [])
    {
        $gitRootDir = $this->mu()->getGitRootDir();
        $this->mu()->execMe(
            $gitRootDir,
            '
            if $(git ls-remote --heads ${REPO} ${BRANCH} | grep -q ' . "'refs/heads/" . $this->nameOfLegacyBranch . "'" . '); then
                    echo branch exists
                else
                    git checkout -b '.$this->nameOfLegacyBranch.';
                    git push origin '.$this->nameOfLegacyBranch.';

            fi',
            'create legacy branch: '.$this->nameOfLegacyBranch.' in '.$gitRootDir,
            false
        );
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
