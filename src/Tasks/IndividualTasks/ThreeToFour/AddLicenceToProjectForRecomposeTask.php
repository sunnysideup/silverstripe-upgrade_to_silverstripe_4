<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks\ThreeToFour;

use Sunnysideup\UpgradeSilverstripe\Tasks\Helpers\ComposerJsonFixes;
use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * Replaces the composer type from silverstripe-module to silverstripe-vendormodule in line with SS4 standards.
 * This means your module will be installed in the vendor folder after this upgrade.
 */
class AddLicenceToProjectForRecomposeTask extends Task
{
    protected $taskStep = 'SS3->SS4';

    public function getTitle()
    {
        return 'Add license to project to ensure recompose works.';
    }

    public function getDescription()
    {
        return '
            Adds the license = proprietary to the composer file to ensure the recompose task works.';
    }

    public function runActualTask($params = []): ?string
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
            $this->setCommitMessage('API:  ' . $this->getTitle());
        }
        return null;
    }

    protected function hasCommitAndPush()
    {
        return $this->mu()->getIsModuleUpgrade() ? false : true;
    }
}
