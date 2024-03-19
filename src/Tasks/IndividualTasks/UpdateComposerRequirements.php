<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\ComposerJsonFixes;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Updates the composer requirements to reflect the new version and package names
 * in the composer file of your module
 */
class UpdateComposerRequirements extends Task
{
    protected $taskStep = 's20';

    protected $package = '';

    protected $newVersion = 'error';

    protected $replacementPackage = '';

    protected $isObsolete = false;

    protected $isNew = false;

    protected $replacementArray = [];

    protected $changed = [
        'silverstripe/recipe-cms',
    ];

    protected $runCommit = true;

    public function getTitle()
    {
        return 'Update composer.json requirements';
    }

    public function getDescription()
    {
        return '
            Change requirements in composer.json file from
            ' . ($this->package ?: 'an Old Package') . ' to ' . ($this->getReplacementPackage() ?: 'a New Package') . ':' . ($this->newVersion ?: ' (and New Version)') . '
            For example, we upgrade silverstripe/framework requirement from 3 to 4.
            Any packages that are not specified will be set to "*".';
    }

    public function runActualTask($params = []): ?string
    {
        if (is_array($this->replacementArray) && count($this->replacementArray)) {
            foreach ($this->replacementArray as $replacementDetails) {
                $this->package = $replacementDetails['package'];
                $this->newVersion = $replacementDetails['newVersion'] ?? 'error';
                $this->isObsolete = $replacementDetails['isObsolete'] ?? false;
                $this->isNew = $replacementDetails['isNew'] ?? false;
                $this->replacementPackage = $replacementDetails['replacementPackage'] ?? '';
                $this->runActualTaskInner();
            }
        } else {
            $this->runActualTaskInner();
        }
        $this->upgradeAllPackages();
        $this->setCommitMessage('API:  upgrading composer requirements to latest versions ... ');
        return null;
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

    protected function runActualTaskInner()
    {
        $package = $this->package;

        // it is possible to run without any changes ...
        if ($package) {
            $this->changed[$package] = $package;
            $this->runCommit = true;

            $newVersion = $this->newVersion;

            $newPackage = $this->getReplacementPackage();

            if ($this->isObsolete) {
                $command =
                    'if(isset($data["require"]["' . $package . '"])) { '
                    . '    unset($data["require"]["' . $package . '"]);'
                    . '}';
                $comment = 'removing the requirement for ' . $package;
            } elseif ($this->isNew) {
                $command = '$data["require"]["' . $newPackage . '"] = "' . $newVersion . '"; ';
                $comment = 'add a NEW package: ' . $package . ' with ' . $newPackage . ':' . $newVersion;
            } else {
                $command =
                    'if(isset($data["require"]["' . $package . '"])) { '
                    . '    unset($data["require"]["' . $package . '"]);'
                    . '    $data["require"]["' . $newPackage . '"] = "' . $newVersion . '"; '
                    . '}';

                $comment = 'replace the require for ' . $package . ' with ' . $newPackage . ':' . $newVersion;
            }

            ComposerJsonFixes::inst($this->mu())->UpdateJSONViaCommandLine(
                $this->mu()->getGitRootDir(),
                $command,
                $comment
            );
        }
    }

    protected function upgradeAllPackages()
    {
        $json = ComposerJsonFixes::inst($this->mu())->getJSON($this->mu()->getGitRootDir());
        foreach ($json['require'] as $package => $version) {
            if (! isset($this->changed[$package])) {
                $json['require'][$package] = '*';
            }
        }
        ComposerJsonFixes::inst($this->mu())->setJSON($this->mu()->getGitRootDir(), $json);
    }

    protected function hasCommitAndPush()
    {
        return $this->runCommit;
    }
}
