<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class AddUpgradeBranch extends Task
{
    protected $taskStep = 's10';

    public function getTitle()
    {
        return 'Creates a start branch for upgrading';
    }

    public function getDescription()
    {
        return '
            Creates a starter branch: "' . $this->mu()->getnameOfUpgradeBranch() . '" of your module/app
            from the ' . $this->mu()->getNameOfBranchForBaseCode() . ' branch.
            These branch names can be customised.
            ';
    }

    /**
     * @param  array  $params not currently used for this task
     */
    public function runActualTask($params = [])
    {
        $gitRootDir = $this->mu()->getGitRootDir();
        $this->mu()->execMe(
            $gitRootDir,
            '
            if $(git ls-remote --heads ${REPO} ${BRANCH} | grep -q ' . "'refs/heads/" . $this->mu()->getNameOfUpgradeBranch() . "'" . '); then
                    echo branch exists
                else
                    git checkout origin ' . $this->mu()->getNameOfBranchForBaseCode() . '
                    git pull origin ' . $this->mu()->getNameOfBranchForBaseCode() . '
                    git checkout -b ' . $this->mu()->getNameOfUpgradeBranch() . '
                    git push origin ' . $this->mu()->getNameOfUpgradeBranch() . '

            fi',
            'create upgrade branch: ' . $this->mu()->getNameOfUpgradeBranch() . ' from '.$this->mu()->getNameOfBranchForBaseCode().' in ' . $gitRootDir,
            false
        );
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
