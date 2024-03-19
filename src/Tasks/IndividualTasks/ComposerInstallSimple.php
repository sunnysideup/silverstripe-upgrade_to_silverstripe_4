<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\FileSystemFixes;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\Composer;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\ComposerJsonFixes;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\Git;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Install a basic / standard install of Silverstripe ('.$this->versionToLoad.')
 * using composer' ;
 */
class ComposerInstallSimple extends Task
{
    protected $taskStep = 's20';

    protected $versionToLoad = '';


    /**
     * @var string
     */
    protected $composerOptions = '--prefer-dist';

    public function getTitle()
    {
        return 'use Composer to install requirements.';
    }

    public function getDescription()
    {
        return '';
    }

    public function runActualTask($params = []): ?string
    {
        $this->mu()->setBreakOnAllErrors(true);
        $command = '
        composer install ;'.$this->composerOptions;
        $this->mu()->execMe(
            $this->mu()->getGitRootDir(),
            $command,
            'Basic composer install',
            false
        );

        $this->mu()->setBreakOnAllErrors(false);
        return null;
    }


    protected function hasCommitAndPush()
    {
        return false;
    }
}
