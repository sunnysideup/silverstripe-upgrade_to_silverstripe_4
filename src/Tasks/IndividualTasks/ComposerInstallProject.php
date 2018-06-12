<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\MetaUpgraderTask;


class ComposerInstallProject extends MetaUpgraderTask
{

    public function upgrade($params = [])
    {
        $this->mo->execMe(
            $this->mo->getABoveWebRootDir(),
            $this->mo->getComposerEnvironmentVars().' composer create-project silverstripe/installer '.$this->mo->getWebRootDir().' ^4',
            'set up vanilla SS4 install',
            false
        );

        $this->mo->execMe(
            $this->mo->getWebRootDir(),
            'git clone '.$this->gitLink.' '.$this->mo->getModuleDir(),
            'cloning module - we clone to keep all vcs data (composer does not allow this for branch)',
            false
        );

        $this->mo->execMe(
            $this->mo->getModuleDir(),
            ' git branch -a ',
            'check branch exists',
            false
        );

        $this->mo->execMe(
            $this->mo->getModuleDir(),
            'git checkout '.$this->mo->getNameOfTempBranch(),
            'switch branch',
            false
        );

        $this->mo->execMe(
            $this->mo->getModuleDir(),
            'git branch ',
            'confirm branch',
            false
        );

            //
            // $this->mo->execMe(
            //     $this->mo->getWebRootDir(),
            //     'composer require '.$this->mo->getVendorName().'/'.$this->mo->getPackageName().':dev-'.$this->mo->getNameOfTempBranch().' --prefer-source', //--prefer-source --keep-vcs
            //     'add '.$this->mo->getVendorName().'/'.$this->mo->getPackageName().':dev-'.$this->mo->getNameOfTempBranch().' to install',
            //     false
            // );
            //
            // $this->mo->getModuleDir() = $this->checkIfPathExistsAndCleanItUp($this->mo->getModuleDir());
            // $this->mo->execMe(
            //     $this->mo->getWebRootDir(),
            //     'rm '.$this->mo->getModuleDir().' -rf',
            //     'we will remove the item again: '.$this->mo->getModuleDir().' so that we can reinstall with vcs data.',
            //     false
            // );
            //
            // $this->mo->execMe(
            //     $this->mo->getWebRootDir(),
            //     'composer update --prefer-source',
            //     'lets retrieve the module again to make sure we have the vcs data with it!',
            //     false
            // );
        }
    }

}
