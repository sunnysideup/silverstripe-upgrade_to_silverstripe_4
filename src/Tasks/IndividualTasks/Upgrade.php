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

    protected $param1 = '';

    protected $param2 = '';

    protected $rootDirForCommand = '';

    /**
     * @todo Prompt does not show up sometimes, leaving the program hanging
     * --prompt
     */
    protected $settings = '';

    public function getTitle()
    {
        return 'Update Code';
    }

    public function getDescription()
    {
        return '
            Runs the silverstripe/upgrade task "upgrade". See:
            Upgrade a variety of stuff (e.g. update reference with namespaces)
            https://github.com/silverstripe/silverstripe-upgrader#upgrade';
    }

    public function runActualTask($params = [])
    {
        foreach ($this->mu()->findNameSpaceAndCodeDirs() as $codeDir) {
            $actualDir = dirname($codeDir);
            $this->param1 = $actualDir;
            $this->runSilverstripeUpgradeTask(
                'upgrade',
                $this->param1,
                $this->param2,
                $this->rootDirForCommand,
                $this->settings
            );
            $this->setCommitMessage('MAJOR: core upgrade to SS4 - STEP 1 (upgrade) on ' . $actualDir);
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
