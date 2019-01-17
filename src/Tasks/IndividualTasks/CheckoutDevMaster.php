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
        return 'Checkout the dev-master of this module.';
    }

    public function getDescription()
    {
        return '
            Checks out dev-master and hopes that framework, etc... will be loaded with it.';
    }

    /**
     *
     * @param  array  $params not currently used for this task
     */
    public function runActualTask($params = [])
    {
        if($this->getIsModuleUpgrade()) {
            $this->mu()->execMe(
                $this->mu()->getWebRootDirLocation(),
                'composer require '.$this->mu()->getVendorName().'/'.$this->mu()->getPackageName().':dev-master  --prefer-source',
                'checkout dev-master of '.$this->mu()->getVendorName().'/'.$this->mu()->getPackageName(),
                false
            );
        } else {
            $this->mu()->execMe(
                $this->mu()->getWebRootDirLocation(),
                'git clone '.$this->mu()->getGitLink().' '.$this->mu()->getGitRootDir(),
                'checkout dev-master of '.$this->mu()->getGitLink(),
                false
            );
        }
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
