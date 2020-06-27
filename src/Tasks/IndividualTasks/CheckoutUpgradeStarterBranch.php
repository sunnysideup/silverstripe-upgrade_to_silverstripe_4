<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\Composer;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\Git;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class CheckoutUpgradeStarterBranch extends Task
{
    protected $taskStep = 's00';

    protected $useGitClone = false;

    protected $composerOptions = '--prefer-source --update-no-dev';

    public function getTitle()
    {
        return 'Checkout the ' . $this->mu()->getNameOfUpgradeStarterBranch() . ' of this module/app.' . "\n" .
            'The name of the branch can be changed by using the following method: setNameOfBranchForBaseCode.' . "\n" .
            'This task may not work if composer is not up-to-date!';
    }

    public function getDescription()
    {
        return '
            Checks out ' . $this->mu()->getNameOfUpgradeStarterBranch() . '
            of project/module using composer for a module or git checkout for a project';
    }

    /**
     * @param  array  $params not currently used for this task
     */
    public function runActualTask($params = [])
    {
        $this->mu()->setBreakOnAllErrors(true);
        if ($this->mu()->getIsModuleUpgrade()) {
            if ($this->useGitClone) {
                $this->gitClone();
            } else {
                $this->composerRequire();
            }
        } else {
            $this->gitClone();
            $this->mu()->execMe(
                $this->mu()->getWebRootDirLocation(),
                'composer info --self',
                'show information about installed project',
                false
            );
        }
        $this->mu()->setBreakOnAllErrors(false);
    }

    protected function gitClone()
    {
        Git::inst($this->mu())
            ->Clone(
                $this->mu()->getWebRootDirLocation(),
                $this->mu()->getGitLink(),
                $this->mu()->getGitRootDir(),
                $this->mu()->getNameOfUpgradeStarterBranch()
            );
    }

    protected function composerRequire()
    {
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'composer init -s dev -n',
            'Start composer - setting it to dev means that it is more likely
            to install dependencies that do not have tags',
            false
        );
        $packageNameFull = $this->mu()->getVendorName() . '/' . $this->mu()->getPackageName();
        $branchAdjusted = 'dev-' . $this->mu()->getNameOfUpgradeStarterBranch();
        Composer::inst($this->mu())
            ->ClearCache()
            ->Require(
                $packageNameFull,
                $branchAdjusted,
                $this->composerOptions
            );
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'composer info ' . $this->mu()->getVendorName() . '/' . $this->mu()->getPackageName(),
            'show information about installed package',
            false
        );
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
