<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\FileSystemFixes;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;
use Sunnysideup\PHP2CommandLine\PHP2CommandLineSingleton;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class SwitchPhpVersion extends Task
{
    protected $taskStep = 's10';

    protected $defaultVersion = '7.1';

    protected $version = '7.1';

    public function getTitle()
    {
        return 'Change PHP version';
    }

    public function getDescription()
    {
        return '
            This requires https://github.com/sunnysideup/silverstripe-switch-php-versions to be installed';
    }

    /**
     * [runActualTask description]
     * @param  array  $params not currently used for this task
     */
    public function runActualTask($params = [])
    {
        $this->version = $params['version'] ?? $this->defaultVersion;
        if(PHP2CommandLineSingleton::commandExists('php-switch')) {
            $this->mu()->execMe(
                $this->mu()->getWebRootDirLocation(),
                'php-switch '.$this->version,
                'switching to PHP version '.$this->version,
                false
            );
        } else {
            $this->mu()->execMe(
                $this->mu()->getWebRootDirLocation(),
                'echo \'switch to PHP Version: '.$this->version . '\'',
                'Reminder to switch to PHP '.$this->version,
                false
            );
        }
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
