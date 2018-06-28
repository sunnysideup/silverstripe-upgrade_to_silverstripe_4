<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class MoveCodeToSRC extends Task
{

    /**
     * [upgrader description]
     * @param  array  $params not currently used for this task
     * @return [type]         [description]
     */
    public function upgrader($params = [])
    {
        $old = $this->mu->getModuleDirLocation().'/code/ ';
        $new = $this->mu->getModuleDirLocation().'src/';
        $this->mu->execMe(
            $this->mu->getModuleDirLocation(),
            'if [ -f '.$old.' ]; then mv '.$old.' '.$new.'; fi',
            'moving '.$old.' to '.$new,
            false
        );

    protected function hasCommit()
    {
        return true;
    }
}
