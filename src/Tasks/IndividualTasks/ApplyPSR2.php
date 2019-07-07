<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Adds a new branch to your repository that is going to be used for upgrading it.
 */
class ApplyPSR2 extends Task
{
    protected $taskStep = 's60';

    public function getTitle()
    {
        return 'Apply PSR2 Cleanup.';
    }

    public function getDescription()
    {
        return '
            Applies a light cleanup of the code to match PSR-2 standards.';
    }

    public function runActualTask($params = [])
    {
        //1. install upgrader
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'composer require --dev symplify/easy-coding-standard',
            'Adding easy coding standards',
            false
        );
        //2. copy ecs.yml
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            $this->mu()->getLocationOfThisUpgrader().'/ecs.yml '.$this->mu()->getWebRootDirLocation().'/',
            'copying ecs.yml file',
            false
        );
        //3. apply
        foreach ($this->mu()->findNameSpaceAndCodeDirs() as $baseNameSpace => $codeDir) {
            $this->mu()->execMe(
                $codeDir,
                'vendor/bin/ecs check ' . $codeDir . ' --fix',
                'Apply PSR-2-etc... to ' . $codeDir . ' (' . $baseNameSpace . ')',
                false
            );
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
