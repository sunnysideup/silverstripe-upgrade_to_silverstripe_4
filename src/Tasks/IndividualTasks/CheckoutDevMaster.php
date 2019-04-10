<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class CheckoutDevMaster extends Task
{
    protected $taskStep = 's00';

    public function getTitle()
    {
        return 'Checkout the '.$this->branchToUse.' of this module.';
    }

    public function getDescription()
    {
        return '
            Checks out '.$this->branchToUse.' of project/module using composer for a module or git checkout for a project';
    }

    protected $branchToUse = 'dev-master';

    /**
     *
     * @param  array  $params not currently used for this task
     */
    public function runActualTask($params = [])
    {
        if($this->mu()->getIsModuleUpgrade()) {
            $this->mu()->execMe(
                $this->mu()->getWebRootDirLocation(),
                'composer init -s dev -n',
                'Start composer - setting it to dev means that it is more likely to install dependencies that do not have tags',
                false
            );
            $this->mu()->execMe(
                $this->mu()->getWebRootDirLocation(),
                'composer require '.$this->mu()->getVendorName().'/'.$this->mu()->getPackageName().':'.$this->branchToUse.'  --prefer-source --update-no-dev ',
                'checkout '.$this->branchToUse.' of '.$this->mu()->getVendorName().'/'.$this->mu()->getPackageName(),
                false
            );
            $this->mu()->execMe(
                $this->mu()->getWebRootDirLocation(),
                'composer info '.$this->mu()->getVendorName().'/'.$this->mu()->getPackageName(),
                'show information about installed package',
                false
            );
        } else {
            $this->mu()->execMe(
                $this->mu()->getWebRootDirLocation(),
                'git clone '.$this->mu()->getGitLink().' '.$this->mu()->getGitRootDir(),
                'clone '.$this->mu()->getGitLink(),
                false
            );
            $this->mu()->execMe(
                $this->mu()->getWebRootDirLocation(),
                'git checkout '.$this->branchToUse,
                'checkout '.$this->branchToUse,
                false
            );
            $this->mu()->execMe(
                $this->mu()->getWebRootDirLocation(),
                'composer info --self',
                'show information about installed project',
                false
            );
        }
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
