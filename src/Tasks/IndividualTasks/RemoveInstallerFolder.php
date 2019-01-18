<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Remove installer folder from composer.json file so that package
 * installs into vendor folder.
 */
class RemoveInstallerFolder extends Task
{
    protected $taskStep = 's20';

    public function getTitle()
    {
        return 'Remove installer-name from composer.json';
    }

    public function getDescription()
    {
        return '
            Remove installer folder from composer.json file so that package
            installs into vendor folder.' ;
    }

    protected $package = '';

    protected $newVersion = '';

    protected $newPackage = '';

    public function runActualTask($params = [])
    {
        if($this->mu()->getIsModuleUpgrade()) {
            $command =
            'if(isset($data["extra"]["installer-name"])) { '
            .'    unset($data["extra"]["installer-name"]);'
            .'}';
            $comment = 'Removing extra.installer-name variable';
            $this->updateJSONViaCommandLine(
                $this->mu()->getGitRootDir(),
                $command,
                $comment
            );
            $this->setCommitMessage('MAJOR: Removing extra.installer-name variable');
        }
    }

    protected function hasCommitAndPush()
    {
        return $this->mu()->getIsModuleUpgrade();
    }
}
