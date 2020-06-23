<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\Composer;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class FixOutdatedPHPStyles extends Task
{
    protected $taskStep = 's10';

    protected $composerOptions = '';

    public function getTitle()
    {
        return 'Uses sunnysideup/huringa to check for outdated styles';
    }

    public function getDescription()
    {
        return '
            See huringa module for mor details';
    }

    public function runActualTask($params = [])
    {
        $webRoot = $this->mu()->getWebRootDirLocation();
        Composer::inst($this->mu())->Require(
            'sunnysideup/huringa',
            'dev-master',
            true,
            $this->composerOptions
        );

        $codeDirs = $this->mu()->findNameSpaceAndCodeDirs();
        foreach ($codeDirs as $codeDir) {
            $this->mu()->execMe(
                $webRoot,
                './vendor/bin/huringa ' . $codeDir,
                'fixing outdated code styles in ' . $codeDir,
                false
            );

            $this->setCommitMessage('MAJOR: fixing outdated code styles using sunnysideup/huringa in ' . $codeDir);
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
