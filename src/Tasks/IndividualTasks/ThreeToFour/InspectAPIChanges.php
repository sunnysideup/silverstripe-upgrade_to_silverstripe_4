<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks\ThreeToFour;

use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * Runs the silverstripe/upgrade task "inspect". See:
 * https://github.com/silverstripe/silverstripe-upgrader#inspect.
 * Once a project has all class names migrated, and is brought up to a
 * "loadable" state (that is, where all classes reference or extend real classes)
 * then the inspect command can be run to perform additional automatic code rewrites.
 * This step will also warn of any upgradable code issues that may prevent a succesful upgrade.
 */
class InspectAPIChanges extends Task
{
    protected $taskStep = 'SS3->SS4';

    protected $param1 = '';

    protected $param2 = '';

    protected $rootDirForCommand = '';

    protected $settings = '';

    public function getTitle()
    {
        return 'After load fixes (inspect)';
    }

    public function getDescription()
    {
        return '
            Runs the silverstripe/upgrade task "inspect". See:
            https://github.com/silverstripe/silverstripe-upgrader#inspect.
            Once a project has all class names migrated, and is brought up to a
            "loadable" state (that is, where all classes reference or extend real classes)
            then the inspect command can be run to perform additional automatic code rewrites.
            This step will also warn of any upgradable code issues that may prevent a succesful upgrade.';
    }

    public function runActualTask($params = []): ?string
    {
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'composer dump-autoload',
            'run composer dump-autoload to create autoload classes',
            false
        );

        $this->mu()->setBreakOnAllErrors(true);

        foreach ($this->mu()->findNameSpaceAndCodeDirs() as $codeDir) {
            $rootDir = '';
            if ($this->mu()->getIsModuleUpgrade()) {
                $dirToRun = $codeDir;
            } else {
                $dirToRun = dirname($codeDir);
            }
            $this->runSilverstripeUpgradeTask(
                'inspect',
                $this->param1 = $dirToRun,
                $this->param2 = '',
                $rootDir,
                $this->settings
            );
            $this->setCommitMessage('API:  core upgrade to SS4: running INSPECT on ' . $this->param1);
        }

        $this->mu()->setBreakOnAllErrors(false);
        return null;
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
