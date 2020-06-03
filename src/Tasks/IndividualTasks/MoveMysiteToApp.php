<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\FileSystemFixes;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class MoveMysiteToApp extends Task
{
    protected $taskStep = 's30';

    public function getTitle()
    {
        return 'Move mysite to app folder for projects';
    }

    public function getDescription()
    {
        return '
            Move the mysite folder to the app folder to match Silverstripe best practice.';
    }

    /**
     * [runActualTask description]
     * @param  array  $params not currently used for this task
     */
    public function runActualTask($params = [])
    {
        if ($this->mu()->getIsProjectUpgrade()) {
            $rootDir = $this->mu()->getWebRootDirLocation();
            $old = '/mysite/';
            $new = '/app/';
            if (file_exists($rootDir . '/' . $old)) {
                if (! file_exists($rootDir . '/' . $new)) {
                    $fixer = new FileSystemFixes($this->mu());
                    $fixer->moveFolderOrFile($rootDir . $old, $rootDir . $new);
                } else {
                    $this->mu()->colourPrint(
                        $rootDir . '/' . $new . ' already exists',
                        'red'
                    );
                }
            } else {
                $this->mu()->colourPrint(
                    'Can not find: ' . $rootDir . '/' . $old,
                    'red'
                );
            }
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
