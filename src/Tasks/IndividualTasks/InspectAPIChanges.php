<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Runs the silverstripe/upgrade task "inpect". See:
 * https://github.com/silverstripe/silverstripe-runActualTask#inspect.
 * Once a project has all class names migrated, and is brought up to a
 * "loadable" state (that is, where all classes reference or extend real classes)
 * then the inspect command can be run to perform additional automatic code rewrites.
 * This step will also warn of any upgradable code issues that may prevent a succesful upgrade.
 */
class InspectAPIChanges extends Task
{
    public function getTitle()
    {
        return 'After load fixes (inspect)';
    }

    public function getDescription()
    {
        return '
            Runs the silverstripe/upgrade task "inpect". See:
            https://github.com/silverstripe/silverstripe-runActualTask#inspect.
            Once a project has all class names migrated, and is brought up to a
            "loadable" state (that is, where all classes reference or extend real classes)
            then the inspect command can be run to perform additional automatic code rewrites.
            This step will also warn of any upgradable code issues that may prevent a succesful upgrade.' ;
    }

    protected $runDir = '';

    protected $param1 = '';

    protected $param2 = '';

    protected $settings = '';

    public function runActualTask($params = [])
    {
        $this->mu->execMe(
            $this->mu->getWebRootDirLocation(),
            'composer dump-autoload',
            'run composer dump-autoload',
            false
        );

        if (empty($this->runDir)) {
            $this->runDir = $this->mu->getWebRootDirLocation();
        }
        if (empty($this->param1)) {
            $this->param1 = $this->mu->findCodeDir();
        }
        $this->runSilverstripeUpgradeTask(
            'inspect',
            $this->runDir,
            $this->param1,
            $this->param2,
            $this->settings
        );
        $this->setCommitMessage('MAJOR: core upgrade to SS4: INSPECT');
    }
}
