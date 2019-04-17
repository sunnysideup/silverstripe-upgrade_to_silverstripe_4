<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class CreatePublicFolder extends Task
{
    protected $taskStep = 's10';

    public function getTitle()
    {
        return 'Create a public folder to match SS4 folder structure';
    }

    public function getDescription()
    {
        return '
            For projects only, we create a public folder: '.$this->mu()->getWebRootDirLocation().'/public' ;
    }

    /**
     * [runActualTask description]
     * @param  array  $params not currently used for this task
     * @return [type]         [description]
     */
    public function runActualTask($params = [])
    {
        if($this->mu->getIsProjectUpgrade()) {
            $this->mu()->execMe(
                $this->mu()->getWebRootDirLocation(),
                'mkdir -v ./public',
                'Creating new public folder: '.$this->mu()->getWebRootDirLocation().'/public',
                false
            );
        }
    }

    protected function hasCommitAndPush()
    {
        return $this->mu->getIsProjectUpgrade();
    }
}