<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Tasks\Helpers\ComposerJsonFixes;
use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * Replaces the composer type from silverstripe-module to silverstripe-vendormodule in line with SS4 standards.
 * This means your module will be installed in the vendor folder after this upgrade.
 */
class UpdateComposerModuleType extends Task
{
    protected $taskStep = 'ANY';

    public function getTitle()
    {
        return 'Update composer type to silverstripe-vendormodule ';
    }

    public function getDescription()
    {
        return '
            Replaces the composer type from silverstripe-module to silverstripe-vendormodule in line with SS4 standards.
            This means your module will be installed in the vendor folder after this upgrade.';
    }

    public function runActualTask($params = []): ?string
    {
        if ($this->mu()->getIsModuleUpgrade()) {
            $command =
                'if(isset($data["type"]) && $data["type"] === "silverstripe-module") { '
                . '    $data["type"] = "silverstripe-vendormodule";'
                . '}';
            $comment = 'Update composer module type from silverstripe-module to silverstripe-vendormodule';
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
        return $this->mu()->getIsModuleUpgrade();
    }
}
