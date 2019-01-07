<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Adds a new branch to your repository that is going to be used for upgrading it.
 */
class ApplyPSR2 extends Task
{
    public function getTitle()
    {
        return 'Apply PSR2 Cleanup.';
    }

    public function getDescription()
    {
        return '
            Applies a light cleanup of the code to match PSR-2 standards.' ;
    }

    public function runActualTask($params = [])
    {
        $this->mu()->execMe(
            $this->mu()->getModuleDirLocation(),
            '
                cd '.$this->mu()->getModuleDirLocation().'
                vendor/bin/php-cs-fixer fix ./src --using-cache=no --rules=@PSR2
            ',
            'Apply PSR-2 to '.$this->mu()->getModuleDirLocation().'/src',
            false
        );
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
