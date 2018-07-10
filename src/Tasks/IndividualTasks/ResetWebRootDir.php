<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class ResetWebRootDir extends Task
{

    public function getTitle()
    {
        return 'Remove and reset Web Root';
    }

    public function getDescription()
    {
        return '
            Delete the web root directory to allow for a fresh install.' ;
    }

    public function runActualTask($params = [])
    {
        $this->mu->execMe(
            $this->mu->getAboveWebRootDirLocation(),
            'rm '.$this->mu->getWebRootDirLocation(). ' -rf',
            'remove the upgrade dir: '.$this->mu->getWebRootDirLocation(),
            false
        );

        $this->mu->execMe(
            $this->mu->getAboveWebRootDirLocation(),
            'mkdir '.$this->mu->getWebRootDirLocation(). '',
            'create upgrade directory: '.$this->mu->getWebRootDirLocation(),
            false
        );
    }

    public function hasCommit()
    {
        return false;
    }
}
