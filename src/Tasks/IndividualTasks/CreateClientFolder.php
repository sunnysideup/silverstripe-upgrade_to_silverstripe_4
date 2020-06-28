<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\FileSystemFixes;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class CreateClientFolder extends Task
{
    protected $taskStep = 's30';

    protected $clientFolderName = 'client';

    public function getTitle()
    {
        return 'Move front-end stuff to a client folder';
    }

    public function getDescription()
    {
        return '
            Takes the javascript, css, and images folders and puts them in a newly created client folder.';
    }

    /**
     * @param  array  $params not currently used for this task
     */
    public function runActualTask($params = [])
    {
        foreach ($this->mu()->getExistingModuleDirLocations() as $moduleDir) {
            $newClientFolder = $moduleDir . '/' . $this->clientFolderName . '/ ';
            $fixer = FileSystemFixes::inst($this->mu())
                ->mkDir($newClientFolder);
            $foldersToMoveName = [
                'javascript',
                'js',
                'images',
                'img',
                'css',
            ];
            foreach ($foldersToMoveName as $folderToMoveName) {
                $folderToMove = $moduleDir . '/' . $folderToMoveName . '/ ';
                $fixer->moveFolderOrFile($folderToMove, $newClientFolder);
            }
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
