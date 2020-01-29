<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;


class FixOutdatedPHPStyles extends Task
{
    protected $taskStep = 's10';

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

        $this->mu()->execMe(
            $webRoot,
            'composer require --dev sunnysideup/huringa:dev-master',
            'adding huringa',
            false
        );


        $codeDirs = $this->mu()->findNameSpaceAndCodeDirs();
        foreach ($codeDirs as $baseNameSpace => $codeDir) {

            $this->mu()->execMe(
                $webRoot,
                './vendor/sunnysideup/huringa/huringa.php '.$codeDir,
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
