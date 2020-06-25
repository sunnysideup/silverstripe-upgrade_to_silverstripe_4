<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\Composer;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\Git;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class CheckoutDevMaster extends Task
{
    protected $taskStep = 's00';

    protected $branchOrTagToUse = 'master';

    protected $useGitClone = false;

    protected $composerOptions = '--prefer-source --update-no-dev';

    public function getTitle()
    {
        return 'Checkout the ' . $this->branchOrTagToUse . ' branch of this module';
    }

    public function getDescription()
    {
        return '
            Checks out ' . $this->branchOrTagToUse . ' (customisable using setNameOfBranchForBaseCode)
            of project/module using composer for a module or git checkout for a project.';
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
                $this->mu()->execMe(
                    $this->mu()->getWebRootDirLocation(),
                    'composer init -s dev -n',
                    'Start composer - setting it to dev means that
                        it is more likely to install dependencies that do not have tags',
                    false
                );
                Composer::inst($this->mu())
                    ->ClearCache()
                    ->Require(
                        $this->mu()->getVendorName() . '/' . $this->mu()->getPackageName(),
                        $this->mu()->getNameOfBranchForBaseCodeForComposer(),
                        false,
                        $this->composerOptions
                    );
                $this->mu()->execMe(
                    $this->mu()->getWebRootDirLocation(),
                    'composer info ' . $this->mu()->getVendorName() . '/' . $this->mu()->getPackageName(),
                    'show information about installed package',
                    false
                );
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
                $this->branchOrTagToUse
            );
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
