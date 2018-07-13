<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class AddLegacyBranch extends Task
{
    public function getTitle()
    {
        return 'Add Legacy Branch';
    }

    public function getDescription()
    {
        return '
            Creates a legacy branch: '.$this->nameOfLegacyBranch.' so that you
            can keep making bugfixes to older versions.';
    }

    /**
     * @var string what should the legacy branch be called
     */
    protected $nameOfLegacyBranch = '3';

    /**
     * [upgrader description]
     * @param  array  $params not currently used for this task
     */
    public function upgrader($params = [])
    {
        $this->mu->execMe(
            $this->mu->getWebRootDirLocation(),
            'composer require '.$this->mu->getVendorName().'/'.$this->mu->getPackageName().':dev-master',
            'checkout dev-master of '.$this->mu->getVendorName().'/'.$this->mu->getPackageName(),
            false
        );

        $this->mu->execMe(
            $this->mu->getModuleDirLocation(),
            '
            if git show-ref --quiet refs/heads/'.$this->nameOfLegacyBranch.';
                then
                    echo "branch exists";
                else
                    git checkout -b '.$this->nameOfLegacyBranch.';
                    git push origin '.$this->nameOfLegacyBranch.';

            fi',
            'create legacy branch: '.$this->nameOfLegacyBranch,
            false
        );
    }

    protected function hasCommit()
    {
        return false;
    }
}
