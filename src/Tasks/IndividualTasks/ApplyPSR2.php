<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\Composer;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Adds a new branch to your repository that is going to be used for upgrading it.
 */
class ApplyPSR2 extends Task
{
    protected $taskStep = 's60';

    protected $composerOptions = '';

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

        Composer::inst($this->mu())->RequireGlobal(
            'sunnysideup/easy-coding-standards',
            'dev-master',
            true,
            $this->composerOptions
        );

        //1. apply
        foreach ($this->mu()->findNameSpaceAndCodeDirs() as $baseNameSpace => $codeDir) {
            $relativeDir = str_replace($webRoot, '', $codeDir);
            $this->mu()->execMe(
                $webRoot,
                'dir=' . $relativeDir . ' sslint-ecs',
                'Apply PSR-2-etc... to ' . $relativeDir . ' (' . $baseNameSpace . ')',
                false
            );
            $this->mu()->execMe(
                $webRoot,
                'level=1 dir=' . $relativeDir . ' sslint-stan > ' . $relativeDir . '/KNOWN_ISSUES',
                'Apply PSR-2-etc... to ' . $relativeDir . ' (' . $baseNameSpace . ') and saving to: ' . $relativeDir . '/KNOWN_ISSUES',
                false
            );
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
