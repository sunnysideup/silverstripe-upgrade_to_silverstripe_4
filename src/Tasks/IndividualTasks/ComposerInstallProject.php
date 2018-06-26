<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class ComposerInstallProject extends Task
{
    public function upgrader($params = [])
    {
        $this->mo->execMe(
            $this->mo->getAboveWebRootDirLocation(),
            $this->mo->getComposerEnvironmentVars().' composer create-project silverstripe/installer '.$this->mo->getWebRootDirLocation().' ^4',
            'set up vanilla SS4 install',
            false
        );

        $this->mo->execMe(
            $this->mo->getWebRootDirLocation(),
            'git clone '.$this->mo->getGitLink().' '.$this->mo->getModuleDirLocation(),
            'cloning module - we clone to keep all vcs data (composer does not allow this for branch)',
            false
        );

        $this->mo->execMe(
            $this->mo->getModuleDirLocation(),
            ' git branch -a ',
            'check branch exists',
            false
        );

        $this->mo->execMe(
            $this->mo->getModuleDirLocation(),
            'git checkout '.$this->mo->getNameOfTempBranch(),
            'switch branch',
            false
        );

        $this->mo->execMe(
            $this->mo->getModuleDirLocation(),
            'git branch ',
            'confirm branch',
            false
        );

        //
            // $this->mo->execMe(
            //     $this->mo->getWebRootDirLocation(),
            //     'composer require '.$this->mo->getVendorName().'/'.$this->mo->getPackageName().':dev-'.$this->mo->getNameOfTempBranch().' --prefer-source', //--prefer-source --keep-vcs
            //     'add '.$this->mo->getVendorName().'/'.$this->mo->getPackageName().':dev-'.$this->mo->getNameOfTempBranch().' to install',
            //     false
            // );
            //
            // $this->mo->getModuleDirLocation() = $this->checkIfPathExistsAndCleanItUp($this->mo->getModuleDirLocation());
            // $this->mo->execMe(
            //     $this->mo->getWebRootDirLocation(),
            //     'rm '.$this->mo->getModuleDirLocation().' -rf',
            //     'we will remove the item again: '.$this->mo->getModuleDirLocation().' so that we can reinstall with vcs data.',
            //     false
            // );
            //
            // $this->mo->execMe(
            //     $this->mo->getWebRootDirLocation(),
            //     'composer update --prefer-source',
            //     'lets retrieve the module again to make sure we have the vcs data with it!',
            //     false
            // );
    }

    protected function hasCommit()
    {
        return false;
    }
}
