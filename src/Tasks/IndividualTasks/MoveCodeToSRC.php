<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\FileSystemFixes;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class MoveCodeToSRC extends Task
{
    protected $taskStep = 's30';

    public function getTitle()
    {
        return 'Move code to src folder';
    }

    public function getDescription()
    {
        return '
            Move the code folder to the src folder to match PSR requirements.';
    }

    /**
     * [runActualTask description]
     * @param  array  $params not currently used for this task
     */
    public function runActualTask($params = [])
    {
        foreach ($this->mu()->getExistingModuleDirLocations() as $moduleDir) {
            $old = '/code/';
            $new = '/src/';
            $fixer = FileSystemFixes::inst($this->mu());
            $fixer->moveFolderOrFile($moduleDir . $old, $moduleDir . $new);
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
