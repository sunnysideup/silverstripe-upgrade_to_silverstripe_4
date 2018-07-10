<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class ComposerInstallProject extends Task
{

    public function getTitle()
    {
        return 'Composer Install Silverstripe 4';
    }


    public function getDescription()
    {
        return '
            Install a basic / standard install of Silverstripe ('.$this->versionToLoad.')
            using composer' ;
    }

    protected $versionToLoad = '^4';

    public function runActualTask($params = [])
    {
        $this->mu->execMe(
            $this->mu->getAboveWebRootDirLocation(),
            $this->mu->getComposerEnvironmentVars().' composer create-project silverstripe/installer '.$this->mu->getWebRootDirLocation().' '.$this->versionToLoad,
            'set up vanilla SS4 install: '.$this->versionToLoad,
            false
        );

        $this->mu->execMe(
            $this->mu->getWebRootDirLocation(),
            'git clone '.$this->mu->getGitLink().' '.$this->mu->getModuleDirLocation(),
            'cloning module - we clone to keep all vcs data (composer does not allow this for branch)',
            false
        );

        $this->mu->execMe(
            $this->mu->getModuleDirLocation(),
            ' git branch -a ',
            'check branch exists',
            false
        );

        $this->mu->execMe(
            $this->mu->getModuleDirLocation(),
            'git checkout '.$this->mu->getNameOfTempBranch(),
            'switch branch',
            false
        );

        $this->mu->execMe(
            $this->mu->getModuleDirLocation(),
            'git branch ',
            'confirm branch',
            false
        );

        //
            // $this->mu->execMe(
            //     $this->mu->getWebRootDirLocation(),
            //     'composer require '.$this->mu->getVendorName().'/'.$this->mu->getPackageName().':dev-'.$this->mu->getNameOfTempBranch().' --prefer-source', //--prefer-source --keep-vcs
            //     'add '.$this->mu->getVendorName().'/'.$this->mu->getPackageName().':dev-'.$this->mu->getNameOfTempBranch().' to install',
            //     false
            // );
            //
            // $this->mu->getModuleDirLocation() = $this->checkIfPathExistsAndCleanItUp($this->mu->getModuleDirLocation());
            // $this->mu->execMe(
            //     $this->mu->getWebRootDirLocation(),
            //     'rm '.$this->mu->getModuleDirLocation().' -rf',
            //     'we will remove the item again: '.$this->mu->getModuleDirLocation().' so that we can reinstall with vcs data.',
            //     false
            // );
            //
            // $this->mu->execMe(
            //     $this->mu->getWebRootDirLocation(),
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
