<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\PHP2CommandLine\PHP2CommandLineSingleton;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\Composer;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;
use Sunnysideup\UpgradeToSilverstripe4\Api\FileSystemFixes;

/**
 * Adds a new branch to your repository that is going to be used for upgrading it.
 */
class ApplyPSR2 extends Task
{
    protected $taskStep = 's60';

    protected $composerOptions = '';

    protected $lintingIssuesFileName = 'LINTING_ERRORS';

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
        $commandAdd = 'vendor/bin';
        if(
            PHP2CommandLineSingleton::commandExists('sslint-ecs') &&
            PHP2CommandLineSingleton::commandExists('sslint-stan')
        ) {

        } else {
            $commandAdd = 'vendor/bin';
            Composer::inst($this->mu())
                ->RequireDev(
                    'sunnysideup/easy-coding-standards',
                    'dev-master',
                    $this->composerOptions
                );
        }

        //1. apply
        foreach ($this->mu()->findNameSpaceAndCodeDirs() as $baseNameSpace => $codeDir) {
            $knownIssuesFileName = $codeDir . '/' . $this->lintingIssuesFileName;
            $relativeDir = str_replace($webRoot, '', $codeDir);
            FileSystemFixes::inst($this->mu())
                ->removeDirOrFile($knownIssuesFileName);
            $this->mu()->execMe(
                $webRoot,
                'dir=' . $relativeDir . ' '.$commandAdd.'sslint-ecs',
                'Apply easy coding standards to ' . $relativeDir . ' (' . $baseNameSpace . ')',
                false
            );
            $this->mu()->execMe(
                $webRoot,
                'dir=' . $relativeDir . ' '.$commandAdd.'sslint-ecs',
                'Apply easy coding standards a second time ' . $relativeDir . ' (' . $baseNameSpace . ')',
                false
            );
            $this->mu()->execMe(
                $webRoot,
                'dir=' . $relativeDir . ' '.$commandAdd.'sslint-ecs > ' . $knownIssuesFileName,
                'Apply easy coding standards a third time ' . $relativeDir . ' (' . $baseNameSpace . ') and saving to ' . $knownIssuesFileName,
                false
            );
            $this->mu()->execMe(
                $webRoot,
                'level=1 dir=' . $relativeDir . ' '.$commandAdd.'sslint-stan >> ' . $knownIssuesFileName,
                'Apply phpstan. to ' . $relativeDir . ' (' . $baseNameSpace . ') and saving to: ' . $knownIssuesFileName,
                false
            );
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
