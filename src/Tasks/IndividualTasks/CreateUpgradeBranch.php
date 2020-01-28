<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class CreateUpgradeBranch extends Task
{
    protected $taskStep = 's10';

    /**
     * @var string what should the legacy branch be called
     */
    protected $madeFrom = 'master';
    /**
     * @var string what should the legacy branch be called
     */
    protected $nameOfUpgradeBranch = 'temp/upgrade/start-branch';

    public function getTitle()
    {
        return 'Creates a start branch for upgrading';
    }

    public function getDescription()
    {
        return '
            Creates a starter branch: "' . $this->nameOfUpgradeBranch . '" of your module/app
            that you edit before you upgrade.
            You can set the name of the legacy branch as you see fit.
            It will be made from the '.$this->madeFrom.' but this branch can be customised too.';
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
            if $(git ls-remote --heads ${REPO} ${BRANCH} | grep -q ' . "'refs/heads/" . $this->nameOfUpgradeBranch . "'" . '); then
                    echo branch exists
                else
                    git checkout origin '.$this->madeFrom.'
                    git pull origin master
                    git checkout -b ' . $this->nameOfUpgradeBranch . ';
                    git push origin ' . $this->nameOfUpgradeBranch . ';

            fi',
            'create upgrade branch: ' . $this->nameOfUpgradeBranch . ' in ' . $gitRootDir,
            false
        );
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
