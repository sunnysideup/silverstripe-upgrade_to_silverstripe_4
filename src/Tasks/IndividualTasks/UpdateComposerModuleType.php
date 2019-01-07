<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Replaces the composer type from silverstripe-module to silverstripe-vendormodule in line with SS4 standards.
 * This means your module will be installed in the vendor folder after this upgrade.
 */
class UpdateComposerModuleType extends Task
{
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


    public function runActualTask($params = [])
    {
        $command =
        'if(isset($data["type"]) && $data["type"] === "silverstripe-module") { '
        .'    $data["type"] = "silverstripe-vendormodule";'
        .'}';
        $comment = 'Update composer module type from silverstripe-module to silverstripe-vendormodule';
        $this->updateJSONViaCommandLine(
            $this->mu()->getModuleDirLocation(),
            $command,
            $comment
        );
        $this->setCommitMessage('MAJOR: '.$this->getTitle());
    }
}
