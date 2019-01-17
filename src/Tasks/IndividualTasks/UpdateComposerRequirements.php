<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Updates the composer requirements to reflect the new version and package names
 * in the composer file of your module
 */
class UpdateComposerRequirements extends Task
{
    protected $taskStep = 's20';

    public function getTitle()
    {
        return 'Update composer.json requirements';
    }

    public function getDescription()
    {
        return '
            Change requirements in composer.json file from
            '.($this->package ?: 'an Old Package').' to '.($this->getReplacementPackage() ?: 'a New Package').':'.($this->newVersion ?: ' (and New Version)').'
            For example, we upgrade silverstripe/framework requirement from 3 to 4.';
    }

    protected $package = '';

    protected $newVersion = '';

    protected $replacementPackage = '';

    public function runActualTask($params = [])
    {
        $package = $this->package;

        $newVersion = $this->newVersion;

        $newPackage = $this->getReplacementPackage();

        $command =
        'if(isset($data["require"]["'.$package.'"])) { '
        .'    unset($data["require"]["'.$package.'"]);'
        .'    $data["require"]["'.$newPackage.'"] = "'.$newVersion.'"; '
        .'}';

        $comment = 'replace the require for '.$package.' with '.$newPackage.':'.$newVersion;

        $this->updateJSONViaCommandLine(
            $this->mu()->getGitRootDir(),
            $command,
            $comment
        );

        $this->setCommitMessage('MAJOR: upgrading composer requirements to SS4 - updating core requirements');
    }


    public function getReplacementPackage()
    {
        if (empty($this->replacementPackage)) {
            $newPackage = $this->package;
        } else {
            $newPackage = $this->replacementPackage;
        }

        return $newPackage;
    }
}
