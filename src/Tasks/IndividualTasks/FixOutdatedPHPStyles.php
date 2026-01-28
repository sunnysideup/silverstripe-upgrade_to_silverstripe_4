<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

class FixOutdatedPHPStyles extends Task
{
    protected $taskStep = 'ANY';

    protected $composerOptions = '';

    public function getTitle()
    {
        return 'Uses sunnysideup/huringa to check for outdated styles. An important part of this is to separate files into separate files / classes.';
    }

    public function getDescription()
    {
        return '
            See huringa module for mor details';
    }

    public function runActualTask($params = []): ?string
    {
        $webRoot = $this->mu()->getWebRootDirLocation();

        $this->mu()->execMe(
            $webRoot,
            'composer require --dev --with-all-dependencies sunnysideup/huringa:dev-master --no-interaction',
            'installing huringa',
            false
        );

        $codeDirs = $this->mu()->findNameSpaceAndCodeDirs();
        $this->mu()->setBreakOnAllErrors(true);
        foreach ($codeDirs as $codeDir) {
            $this->mu()->execMe(
                $webRoot,
                'vendor/bin/huringa ' . $codeDir,
                'fixing outdated code styles in ' . $codeDir,
                false
            );

            $this->setCommitMessage('API:  fixing outdated code styles using sunnysideup/huringa in ' . $codeDir);
        }

        $this->mu()->execMe(
            $webRoot,
            'composer remove sunnysideup/huringa:dev-master',
            'uninstalling huringa',
            false
        );

        $this->mu()->setBreakOnAllErrors(false);
        return null;
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
