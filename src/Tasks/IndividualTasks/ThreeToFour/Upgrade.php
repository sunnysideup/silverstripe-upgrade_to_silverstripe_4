<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks\ThreeToFour;

use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * Runs the silverstripe upgrade task 'upgrade'.
 * More information on this task at https://github.com/silverstripe/silverstripe-upgrader#upgrade
 */
class Upgrade extends Task
{
    protected $taskStep = 'SS3->SS4';

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

    public function runActualTask($params = []): ?string
    {
        $this->mu()->setBreakOnAllErrors(true);
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
            $this->setCommitMessage('API:  core upgrade to SS4 - STEP 1 (upgrade) on ' . $actualDir);
        }
        $this->mu()->setBreakOnAllErrors(false);
        return null;
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
