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
        $webRoot = $this->mu()->getWebRootDirLocation();
        //1. install upgrader
        $this->mu()->execMe(
            $webRoot,
            'composer require --dev sunnysideup/easy-coding-standards:dev-master',
            'Adding easy coding standards',
            false
        );
        //1. apply
        foreach ($this->mu()->findNameSpaceAndCodeDirs() as $baseNameSpace => $codeDir) {
            $relativeDir = str_replace($webRoot, '', $codeDir);
            $this->mu()->execMe(
                $webRoot,
                'dir=' . $relativeDir . ' vendor/bin/php-sslint-ecs',
                'Apply PSR-2-etc... to ' . $relativeDir . ' (' . $baseNameSpace . ')',
                false
            );
            $this->mu()->execMe(
                $webRoot,
                'dir=' . $relativeDir . ' vendor/bin/php-sslint-stan',
                'Apply PSR-2-etc... to ' . $relativeDir . ' (' . $baseNameSpace . ')',
                false
            );
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
