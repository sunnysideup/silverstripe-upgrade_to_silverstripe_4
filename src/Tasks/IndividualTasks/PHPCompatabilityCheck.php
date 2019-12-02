<?php

//requires https://github.com/squizlabs/PHP_CodeSniffer
//requires https://github.com/PHPCompatibility/PHPCompatibility

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Delete the web root directory to allow for a fresh install.
 */
class PHPCompatabilityCheck extends Task
{
    protected $taskStep = 's00';

    public function getTitle()
    {
        return 'PHP Compatibility Check';
    }

    public function getDescription()
    {
        return 'Outputs a file showing errors prevent code from being compatible with php7.2';
    }

    public function runActualTask($params = [])
    {
        $webRoot = $this->mu()->getWebRootDirLocation();
        //@todo do this later
        //1. install upgrader
        // $this->mu()->execMe(
        //     $webRoot,
        //     'composer require --dev symplify/easy-coding-standard',
        //     'Adding easy coding standards',
        //     false
        // );
        // //2. copy ecs.yml
        // $this->mu()->execMe(
        //     $webRoot,
        //     'cp '.$this->mu()->getLocationOfThisUpgrader().'/ecs.yml '.$webRoot.'/',
        //     'copying ecs.yml file',
        //     false
        // );
        //3. apply
        foreach ($this->mu()->findNameSpaceAndCodeDirs() as $baseNameSpace => $codeDir) {
            $this->mu()->execMe(
                $webRoot,
                'phpcs -p ' . $codeDir . ' --standard=PHPCompatibility --extensions=php --runtime-set testVersion 7.2 --report-full='. $this->mu()->getLogFolderDirLocation() . '/' . $baseNameSpace . '-php-compatibility-report -n',
                'Running PHP Compatibility Check in: ' . $this->mu()->getWebRootDirLocation(),
                false
            );
        }
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
