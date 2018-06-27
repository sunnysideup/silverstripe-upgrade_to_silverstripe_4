<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class AddLegacyBranch extends Task
{
    /**
     * @var string what should the legacy branch be called
     */
    protected $nameOfLegacyBranch = '3';

    /**
     * [upgrader description]
     * @param  array  $params not currently used for this task
     * @return [type]         [description]
     */
    public function upgrader($params = [])
    {
        $this->mo->execMe(
            $this->mo->getWebRootDirLocation(),
            'composer require '.$this->mo->getVendorName().'/'.$this->mo->getPackageName().':dev-master',
            'checkout dev-master of '.$this->mo->getVendorName().'/'.$this->mo->getPackageName(),
            false
        );

        $this->mo->execMe(
            $this->mo->getModuleDirLocation(),
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
