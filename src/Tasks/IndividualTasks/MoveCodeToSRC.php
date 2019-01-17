<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

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
            Move the code folder to the src folder to match PSR requirements.' ;
    }

    /**
     * [runActualTask description]
     * @param  array  $params not currently used for this task
     * @return [type]         [description]
     */
    public function runActualTask($params = [])
    {
        foreach($this->mu()->getExistingModuleDirLocations() as $moduleDir) {
            $old = $moduleDir.'/code/ ';
            $new = $moduleDir.'/src/';
            $this->mu()->execMe(
                $this->mu()->getModuleDirLocation(),
                'if test -d '.$old.'; then mv -vn '.$old.' '.$new.'; fi;',
                'moving '.$old.' to '.$new.' -v is verbose, -n is only if destination does not exists',
                false
            );
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
