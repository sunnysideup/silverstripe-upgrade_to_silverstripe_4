<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class AddUpgradeBranch extends Task
{

    public function getTitle()
    {
        return 'Add Upgrade Branch';
    }

    public function getDescription()
    {
        return '
            Adds a new branch ('.$this->mu->getNameOfTempBranch().') to your
            repository ('.$this->mu->getVendorName().'/'.$this->mu->getPackageName().')
            that is going to be used for upgrading it.' ;
    }

    public function runActualTask($params = [])
    {
        $this->mu->execMe(
            $this->mu->getWebRootDirLocation(),
            'composer require '.$this->mu->getVendorName().'/'.$this->mu->getPackageName().':dev-master',
            'checkout dev-master of '.$this->mu->getVendorName().'/'.$this->mu->getPackageName(),
            false
        );

        $this->mu->execMe(
            $this->mu->getModuleDirLocation(),
            'if git show-ref --quiet refs/heads/'.$this->mu->getNameOfTempBranch().'; then git branch -d '.$this->mu->getNameOfTempBranch().'; git push origin --delete '.$this->mu->getNameOfTempBranch().'; fi',
            'delete upgrade branch ('.$this->mu->getNameOfTempBranch().') locally',
            false
        );

        $this->mu->execMe(
            $this->mu->getModuleDirLocation(),
            'git push origin --delete '.$this->mu->getNameOfTempBranch(),
            'delete upgrade branch ('.$this->mu->getNameOfTempBranch().') remotely',
            false
        );

        $this->mu->execMe(
            $this->mu->getModuleDirLocation(),
            'git checkout -b '.$this->mu->getNameOfTempBranch(),
            'create and checkout new branch: '.$this->mu->getNameOfTempBranch(),
            false
        );
    }

    protected function hasCommit()
    {
        return false;
    }
}
