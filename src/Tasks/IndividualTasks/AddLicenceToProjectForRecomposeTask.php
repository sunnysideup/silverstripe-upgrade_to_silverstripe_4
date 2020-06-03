<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\ComposerJsonFixes;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Replaces the composer type from silverstripe-module to silverstripe-vendormodule in line with SS4 standards.
 * This means your module will be installed in the vendor folder after this upgrade.
 */
class AddLicenceToProjectForRecomposeTask extends Task
{
    protected $taskStep = 's50';

    public function getTitle()
    {
        return 'Add license to project to ensure recompose works.';
    }

    public function getDescription()
    {
        return '
            Adds the license = proprietary to the composer file to ensure the recompose task works.';
    }

    public function runActualTask($params = [])
    {
        if ($this->mu()->getIsProjectUpgrade()) {
            $comment = 'add license';
            $command =
            'if(! isset($data["license"])) { '
            . '    $data["license"] = proprietary";'
            . '}';
            ComposerJsonFixes::inst($this->mu())->UpdateJSONViaCommandLine(
                $this->mu()->getGitRootDir(),
                $command,
                $comment
            );
            $this->setCommitMessage('MAJOR: ' . $this->getTitle());
        }
    }

    protected function hasCommitAndPush()
    {
        return $this->mu()->getIsModuleUpgrade() ? false : true;
    }
}
