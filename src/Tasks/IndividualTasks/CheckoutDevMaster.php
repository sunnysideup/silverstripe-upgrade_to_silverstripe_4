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

    /**
     * overruled by NameOfBranchForBaseCode
     *
     * @var string
     */
    protected $branchOrTagToUse = 'master';

    protected $composerOptions = '--prefer-source --update-no-dev';

    public function getTitle()
    {
        return 'Checkout the ' . $this->branchOrTagToUse . ' branch of this module';
    }

    public function getDescription()
    {
        return '
Checks out ' . $this->branchOrTagToUse . ' (customisable using setNameOfBranchForBaseCode)
of project/module using composer for a module or git checkout for a project.
=============================================================================
NB: this branch may just be created and so composer may fail here,
simply start again in a few minutes in this case to make it work.
=============================================================================';
    }

    /**
     * @param  array  $params not currently used for this task
     */
    public function runActualTask($params = []): ?string
    {
        $alternativeCodeBase = $this->mu()->getNameOfBranchForBaseCode();
        if ($alternativeCodeBase) {
            $this->branchOrTagToUse = $alternativeCodeBase;
        }
        $this->mu()->setBreakOnAllErrors(true);
        if ($this->mu()->getIsModuleUpgrade()) {
            if ($this->mu()->getisOnPackagist() !== true) {
                $this->gitClone();
            } else {
                $this->mu()->execMe(
                    $this->mu()->getWebRootDirLocation(),
                    'composer init -s dev -n --no-interaction',
                    'Start composer - setting it to dev means that
                        it is more likely to install dependencies that do not have tags',
                    false
                );
                Composer::inst($this->mu())
                    ->ClearCache()
                    ->Require(
                        $this->mu()->getVendorName() . '/' . $this->mu()->getPackageName(),
                        $this->mu()->getNameOfBranchForBaseCodeForComposer(),
                        $this->composerOptions
                    );
                $this->mu()->execMe(
                    $this->mu()->getWebRootDirLocation(),
                    'composer info ' . $this->mu()->getVendorName() . '/' . $this->mu()->getPackageName() . ' --no-interaction',
                    'show information about installed package',
                    false
                );
            }
        } else {
            $this->gitClone();
            $this->mu()->execMe(
                $this->mu()->getWebRootDirLocation(),
                'composer info --self --no-interaction',
                'show information about installed project',
                false
            );
        }
        $this->mu()->setBreakOnAllErrors(false);
        return null;
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
