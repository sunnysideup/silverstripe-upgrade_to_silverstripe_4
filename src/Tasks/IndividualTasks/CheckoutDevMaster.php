<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class CheckoutDevMaster extends Task
{
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
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'composer require '.$this->mu()->getVendorName().'/'.$this->mu()->getPackageName().':dev-master  --prefer-source',
            'checkout dev-master of '.$this->mu()->getVendorName().'/'.$this->mu()->getPackageName(),
            false
        );
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
