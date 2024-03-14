<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\PHP2CommandLine\PHP2CommandLineSingleton;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class LintAll extends Task
{
    protected $taskStep = 's30';

    public function getTitle()
    {
        return 'Lint all php code.';
    }

    public function getDescription()
    {
        return '
            Goes through all the folders and uses the sake-lint-all function (this will need to be installed).';
    }

    /**
     * [runActualTask description]
     * @param  array  $params not currently used for this task
     */
    public function runActualTask($params = []): ?string
    {
        if(PHP2CommandLineSingleton::commandExists('sake-lint-all')) {
            foreach ($this->mu()->getExistingModuleDirLocations() as $moduleDir) {
                $this->mu()->execMe(
                    $this->mu()->getWebRootDirLocation(),
                    'sake-lint-all ' . $moduleDir,
                    'Linting all PHP files in ' . $moduleDir,
                    true
                );
            }
        } else {
            return 'You need to install sake-lint-all to use this task: https://github.com/sunnysideup/silverstripe-easy-coding-standards';
        }
        return null;
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
