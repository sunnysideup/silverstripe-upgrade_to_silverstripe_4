<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\MetaUpgraderTask;

class AddLegacyBranch extends MetaUpgraderTask
{
    protected $nameOfLegacyBranch = '3';

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
