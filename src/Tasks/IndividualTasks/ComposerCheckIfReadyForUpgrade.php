<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Tasks\Helpers\ComposerCompatibilityUpdater;
use Sunnysideup\UpgradeSilverstripe\Tasks\Helpers\ComposerJsonFixes;
use Sunnysideup\UpgradeSilverstripe\Tasks\Helpers\SilverstripeCorePackageInfo;
use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

class ComposerCheckIfReadyForUpgrade extends Task
{
    protected $taskStep = 'ANY';

    public function getTitle()
    {
        return 'Check if this package can be updated.
        If any of its requirements is not ready then the answer is no as these should be done first.';
    }

    protected int $silverstripeVersion = 4;

    public function getDescription()
    {
        return 'Goes through all of the required packages in composer.json and checks if
        all of them have a compatible version for the intended upgrade.';
    }

    /**
     * @param array $params
     */
    public function runActualTask($params = []): ?string
    {
        $this->mu()->setBreakOnAllErrors(true);
        $jsonDir = $this->mu()->getGitRootDir();
        $json = ComposerJsonFixes::inst($this->mu())->getJson($jsonDir);
        $corePackages = SilverstripeCorePackageInfo::get_core_packages($this->silverstripeVersion);

        // check if done already...
        $isAlreadyUpgraded = ComposerCompatibilityUpdater::inst($this->mu())
            ->isAlreadyUpgraded($json, $corePackages, $this->silverstripeVersion);
        if ($isAlreadyUpgraded) {
            $this->mu()->colourPrint('This package is already upgraded.', 'green');
            exit(0);
        }

        // check if it can be done right now...
        $incompatiblePackages = ComposerCompatibilityUpdater::inst($this->mu())
            ->findIncompatiblePackages($json, $corePackages, $this->silverstripeVersion);
        if (!empty($incompatiblePackages)) {
            user_error('Not all packages are ready for upgrade: ' . implode(', ', $incompatiblePackages));
        }
        $this->mu()->setBreakOnAllErrors(false);

        return null;
    }


    protected function hasCommitAndPush()
    {
        return false;
    }
}
