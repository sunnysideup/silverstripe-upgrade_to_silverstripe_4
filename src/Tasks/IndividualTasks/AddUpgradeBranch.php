<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\MetaUpgraderTask;


class AddUpgradeBranch extends MetaUpgraderTask
{

    public function upgrade($params = [])
    {
        $this->mo->execMe(
            $this->mo->getWebRootDir(),
            'composer require '.$this->mo->getVendorName().'/'.$this->mo->getPackageName().':dev-master',
            'checkout dev-master of '.$this->mo->getVendorName().'/'.$this->mo->getPackageName(),
            false
        );

        $this->mo->execMe(
            $this->mo->getModuleDir(),
            'if git show-ref --quiet refs/heads/'.$this->mo->getNameOfTempBranch().'; then git branch -d '.$this->mo->getNameOfTempBranch().'; git push origin --delete '.$this->mo->getNameOfTempBranch().'; fi',
            'delete upgrade branch ('.$this->mo->getNameOfTempBranch().') locally',
            false
        );

        $this->mo->execMe(
            $this->mo->getModuleDir(),
            'git push origin --delete '.$this->mo->getNameOfTempBranch(),
            'delete upgrade branch ('.$this->mo->getNameOfTempBranch().') remotely',
            false
        );

        $this->mo->execMe(
            $this->mo->getModuleDir(),
            'git checkout -b '.$this->mo->getNameOfTempBranch(),
            'create and checkout new branch: '.$this->mo->getNameOfTempBranch(),
            false
        );

    }

    protected function hasCommit()
    {
        return false;
    }

}
