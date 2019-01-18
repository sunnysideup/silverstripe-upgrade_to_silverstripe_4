<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Runs the silverstripe/upgrade task "inpect". See:
 * https://github.com/silverstripe/silverstripe-upgrader#inspect.
 * Once a project has all class names migrated, and is brought up to a
 * "loadable" state (that is, where all classes reference or extend real classes)
 * then the inspect command can be run to perform additional automatic code rewrites.
 * This step will also warn of any upgradable code issues that may prevent a succesful upgrade.
 */
class InspectAPIChanges extends Task
{
    protected $taskStep = 's50';

    public function getTitle()
    {
        return 'After load fixes (inspect)';
    }

    public function getDescription()
    {
        return '
            Runs the silverstripe/upgrade task "inpect". See:
            https://github.com/silverstripe/silverstripe-upgrader#inspect.
            Once a project has all class names migrated, and is brought up to a
            "loadable" state (that is, where all classes reference or extend real classes)
            then the inspect command can be run to perform additional automatic code rewrites.
            This step will also warn of any upgradable code issues that may prevent a succesful upgrade.' ;
    }

    protected $param1 = '';

    protected $param2 = '';

    protected $rootDirForCommand = '';

    protected $settings = '';

    public function runActualTask($params = [])
    {
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'composer dump-autoload',
            'run composer dump-autoload to create autoload classes',
            false
        );

        foreach($this->mu()->findNameSpaceAndCodeDirs() as $baseNameSpace => $codeDir) {
            $this->param1 = $codeDir;
            $this->runSilverstripeUpgradeTask(
                'inspect',
                $this->param1,
                $this->param2,
                $this->rootDirForCommand,
                $this->settings
            );
            $this->setCommitMessage('MAJOR: core upgrade to SS4: INSPECT');
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
