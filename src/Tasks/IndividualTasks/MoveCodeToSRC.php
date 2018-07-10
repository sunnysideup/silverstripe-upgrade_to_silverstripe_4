<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class MoveCodeToSRC extends Task
{

    public function getTitle()
    {
        return 'Move code to src folder';
    }

    public function getDescription()
    {
        return '
            Move code folder to src folder to match PSR requirements.' ;
    }

    /**
     * [runActualTask description]
     * @param  array  $params not currently used for this task
     * @return [type]         [description]
     */
    public function runActualTask($params = [])
    {
        $old = $this->mu->getModuleDirLocation().'/code/ ';
        $new = $this->mu->getModuleDirLocation().'/src/';
        $this->mu->execMe(
            $this->mu->getModuleDirLocation(),
            'mv -vn '.$old.' '.$new.'',
            'moving '.$old.' to '.$new.' -v is verbose, -n is only if src does not exists',
            false
        );
    }

    protected function hasCommit()
    {
        return true;
    }
}
