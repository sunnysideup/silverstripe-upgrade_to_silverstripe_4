<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class ResetWebRootDir extends Task
{
    public function upgrader($params = [])
    {
        $this->mo->execMe(
            $this->mo->getAboveWebRootDirLocation(),
            'rm '.$this->mo->getWebRootDirLocation(). ' -rf',
            'remove the upgrade dir: '.$this->mo->getWebRootDirLocation(),
            false
        );

        $this->mo->execMe(
            $this->mo->getAboveWebRootDirLocation(),
            'mkdir '.$this->mo->getWebRootDirLocation(). '',
            'create upgrade directory: '.$this->mo->getWebRootDirLocation(),
            false
        );
    }

    public function hasCommit()
    {
        return false;
    }
}
