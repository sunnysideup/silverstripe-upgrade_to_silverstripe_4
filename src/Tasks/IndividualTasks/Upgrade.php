<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Runs the silverstripe upgrade task 'upgrade'.
 * More information on this task at https://github.com/silverstripe/silverstripe-upgrader#upgrade
 */
class Upgrade extends Task
{
    protected $taskStep = 's40';

    public function getTitle()
    {
        return 'Update Code';
    }


    public function getDescription()
    {
        return '
            Runs the silverstripe/upgrade task "upgrade". See:
            Upgrade a variety of stuff (e.g. update reference with namespaces)
            https://github.com/silverstripe/silverstripe-upgrader#upgrade' ;
    }

    protected $param1 = '';

    protected $param2 = '';

    protected $rootDirForCommand = '';

    protected $settings = '';

    public function runActualTask($params = [])
    {
        foreach($this->mu()->findNameSpaceAndCodeDirs() as $baseNameSpace => $codeDir) {
            $this->param1 = $codeDir;
            $this->runSilverstripeUpgradeTask(
                'upgrade',
                $this->param1,
                $this->param2,
                $this->rootDirForCommand,
                $this->settings
            );
            $this->setCommitMessage('MAJOR: core upgrade to SS4 - STEP 1 (upgrade) on '.$codeDir);
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
