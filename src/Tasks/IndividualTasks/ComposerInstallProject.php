<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\Git;

/**
 * Install a basic / standard install of Silverstripe ('.$this->versionToLoad.')
 * using composer' ;
 */
class ComposerInstallProject extends Task
{
    protected $taskStep = 's20';

    protected $versionToLoad = '';

    public function getTitle()
    {
        return 'use Composer to install vanilla Silverstripe project and add project / module to it.';
    }

    public function getDescription()
    {
        return '
            Install a basic / standard install of Silverstripe (' . ($this->versionToLoad ?: $this->mu()->getFrameworkComposerRestraint()) . ')
            using composer and install module / project into the vanilla silverstripe install.';
    }

    public function runActualTask($params = [])
    {
        if (! $this->versionToLoad) {
            $this->versionToLoad = $this->mu()->getFrameworkComposerRestraint();
        }
        $cloneDir = $this->mu()->getGitRootDir();
        if ($this->mu()->getIsModuleUpgrade()) {
            $this->mu()->execMe(
                $this->mu()->getAboveWebRootDirLocation(),
                $this->mu()->getComposerEnvironmentVars() . ' composer create-project silverstripe/installer ' . $this->mu()->getWebRootDirLocation() . ' ' . $this->versionToLoad,
                'set up vanilla install using version: ' . $this->versionToLoad,
                false
            );
        }
        Git::inst($this->mu())
            ->Clone(
                $this->mu()->getWebRootDirLocation(),
                $this->mu()->getGitLink(),
                $this->mu()->getGitRootDir(),
                $this->mu()->getNameOfTempBranch()
            );
        if ($this->mu()->getIsProjectUpgrade()) {
            $this->mu()->execMe(
                $cloneDir,
                'composer update -vvv',
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
