<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
* Install a basic / standard install of Silverstripe ('.$this->versionToLoad.')
* using composer' ;
*/
class ComposerInstallProject extends Task
{
    protected $taskStep = 's20';

    public function getTitle()
    {
        return 'Composer Install Silverstripe 4';
    }


    public function getDescription()
    {
        return '
            Install a basic / standard install of Silverstripe ('.($this->versionToLoad ? : $this->mu()->getFrameworkComposerRestraint()).')
            using composer' ;
    }

    protected $versionToLoad = '';

    public function runActualTask($params = [])
    {
        if(! $this->versionToLoad) {
            $this->versionToLoad = $this->mu()->getFrameworkComposerRestraint();
        }
        $cloneDir = $this->mu()->getGitRootDir();
        if($this->mu()->getIsModuleUpgrade()) {
            $this->mu()->execMe(
                $this->mu()->getAboveWebRootDirLocation(),
                $this->mu()->getComposerEnvironmentVars().' composer create-project silverstripe/installer '.$this->mu()->getWebRootDirLocation().' '.$this->versionToLoad,
                'set up vanilla SS4 install: '.$this->versionToLoad,
                false
            );
        }

        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'git clone '.$this->mu()->getGitLink().' '.$cloneDir,
            'cloning module '.$this->mu()->getGitLink().' into '.$cloneDir.' - we clone to keep all vcs data (composer does not allow this for branch)',
            false
        );

        $this->mu()->execMe(
            $cloneDir,
            'git branch -a ',
            'check branch exists',
            false
        );

        $this->mu()->execMe(
            $cloneDir,
            'git checkout '.$this->mu()->getNameOfTempBranch(),
            'switch branch',
            false
        );

        $this->mu()->execMe(
            $cloneDir,
            'git branch',
            'confirm branch',
            false
        );
        if($this->mu()->getIsProjectUpgrade()) {
            $this->mu()->execMe(
                $cloneDir,
                'composer update',
                'run composer update',
                false
            );
        }

    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
