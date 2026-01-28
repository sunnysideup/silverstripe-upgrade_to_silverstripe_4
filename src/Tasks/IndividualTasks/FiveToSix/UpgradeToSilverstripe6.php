<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks\FiveToSix;

use Sunnysideup\PHP2CommandLine\PHP2CommandLineSingleton;
use Sunnysideup\UpgradeSilverstripe\Api\FileSystemFixes;
use Sunnysideup\UpgradeSilverstripe\Tasks\Helpers\Composer;
use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * Adds a new branch to your repository that is going to be used for upgrading it.
 */
class UpgradeToSilverstripe6 extends Task
{
    protected $taskStep = 'SS5->SS6';

    protected $composerOptions = '';

    protected $lintingIssuesFileName = 'LINTING_ERRORS';

    public function getTitle()
    {
        return 'Upgrade to Silverstripe 6.';
    }

    public function getDescription()
    {
        return '
            Runs the basic upgrade to Silverstripe 6 code changes.';
    }

    public function runActualTask($params = []): ?string
    {
        $webRoot = $this->mu()->getWebRootDirLocation();
        $isInstalled = (bool) PHP2CommandLineSingleton::commandExists('sake-lint-rector');
        $commandAdd = '';
        if (!$isInstalled) {
            $commandAdd = 'vendor/bin/';
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
            $relativeDir = ltrim($relativeDir, '/');
            FileSystemFixes::inst($this->mu())
                ->removeDirOrFile($knownIssuesFileName);
            $this->mu()->execMe(
                $webRoot,
                $commandAdd . 'sake-lint-rector  -r ./RectorSS6.php   ' . $relativeDir,
                'Apply easy coding standards to ' . $relativeDir . ' (' . $baseNameSpace . ')',
                false
            );
        }
        if ($isInstalled) {
            Composer::inst($this->mu())
                ->RemoveDev(
                    'sunnysideup/easy-coding-standards',
                    'dev-master',
                    $this->composerOptions
                );
        }
        return null;
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
