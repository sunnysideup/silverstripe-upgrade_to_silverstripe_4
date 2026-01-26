<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Tasks\Helpers\ComposerJsonFixes;
use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * Remove installer folder from composer.json file so that package
 * installs into vendor folder.
 */
class RemoveInstallerFolder extends Task
{
    protected $taskStep = 's20';

    protected $package = '';

    protected $newVersion = '';

    protected $newPackage = '';

    public function getTitle()
    {
        return 'Remove installer-name from composer.json';
    }

    public function getDescription()
    {
        return '
            Remove installer folder from composer.json file so that package
            installs into vendor folder.';
    }

    public function runActualTask($params = []): ?string
    {
        if ($this->mu()->getIsModuleUpgrade()) {
            $command =
                'if(isset($data["extra"]["installer-name"])) { '
                . '    unset($data["extra"]["installer-name"]);'
                . '}';
            $comment = 'Removing extra.installer-name variable';
            ComposerJsonFixes::inst($this->mu())->UpdateJSONViaCommandLine(
                $this->mu()->getGitRootDir(),
                $command,
                $comment
            );
            $this->setCommitMessage('API:  Removing extra.installer-name variable');
        }
        return null;
    }

    protected function hasCommitAndPush()
    {
        return $this->mu()->getIsModuleUpgrade();
    }
}
