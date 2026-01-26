<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Api\FileSystemFixes;
use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

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
    public function runActualTask($params = []): ?string
    {
        if ($this->mu()->getIsProjectUpgrade()) {
            $rootDir = $this->mu()->getWebRootDirLocation();
            $old = '/mysite/';
            $new = '/app/';
            $oldPath = $this->normalisedDir($rootDir . $old);
            if (file_exists($oldPath)) {
                $newPath = $this->normalisedDir($rootDir . $new);
                if (! file_exists($newPath)) {
                    $fixer = FileSystemFixes::inst($this->mu());
                    $fixer->moveFolderOrFile($oldPath, $newPath);
                } else {
                    $this->mu()->colourPrint($newPath . ' already exists', 'red');
                }
            } else {
                $this->mu()->colourPrint(
                    'Can not find: ' . $rootDir . '/' . $old,
                    'red'
                );
            }
        }
        return null;
    }

    protected function hasCommitAndPush()
    {
        return true;
    }

    protected function normalisedDir(string $path): string
    {
        $normalizedPath = str_replace(['//', '/', '\\\\', '\\'], DIRECTORY_SEPARATOR, $path);
        return realpath($normalizedPath);
    }
}
