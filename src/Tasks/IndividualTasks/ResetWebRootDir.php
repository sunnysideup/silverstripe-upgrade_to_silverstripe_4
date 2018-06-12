<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\MetaUpgraderTask;

class ResetWebRootDir extends MetaUpgraderTask
{
    public function upgrader($params = [])
    {
        $this->mo->execMe(
            $this->mo->getABoveWebRootDir(),
            'rm '.$this->mo->getWebRootDir(). ' -rf',
            'remove the upgrade dir: '.$this->mo->getWebRootDir(),
            false
        );

        $this->mo->execMe(
            $this->mo->getABoveWebRootDir(),
            'mkdir '.$this->mo->getWebRootDir(). '',
            'create upgrade directory: '.$this->mo->getWebRootDir(),
            false
        );
    }
}
