<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\ComposerJsonFixes;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\Git;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class MakeRequirementsMoreFlexible extends Task
{
    protected $taskStep = 's00';

    public function getTitle()
    {
        return 'Make requirements more flexible by changing requirements from, for example, 3.6.2 to ^3.6.2';
    }

    public function getDescription()
    {
        return '
Goes through all the requirements in the composer.json file and changes them from, for example, 3.6.2 to ^3.6.2.
Also checks dev requirements.
Runs a composer update at the end.
';
    }

    /**
     * @param  array  $params not currently used for this task
     */
    public function runActualTask($params = []): ?string
    {
        $this->mu()->setBreakOnAllErrors(true);
        $this->updateComposerJson();
        $this->mu()->setBreakOnAllErrors(false);
        return null;
    }

    protected function hasCommitAndPush()
    {
        return true;
    }

    public function updateComposerJson()
    {
        $composerData = ComposerJsonFixes::inst($this->mu())->getJSON(
            $this->mu()->getGitRootDir()
        );
        foreach (['require', 'require-dev'] as $section) {
            if (isset($composerData[$section]) && is_array($composerData[$section]) && count($composerData[$section])) {
                foreach ($composerData[$section] as $package => &$version) {
                    if (strpos($version, 'silverstripe-australia') !== false) {
                        $newVersion = str_replace('silverstripe-australia', 'symbiote', $version);
                        $this->mu()->colourPrint('replacing ' . $package . ':' . $version . ' with ' . $package . ':' . $newVersion, 'green', 1);
                        $version = $newVersion;
                    }
                    if (strpos($version, '.x') !== false) {
                        $newVersion = str_replace('.x', '.0', $version);
                        $this->mu()->colourPrint('replacing ' . $package . ':' . $version . ' with ' . $package . ':' . $newVersion, 'green', 1);
                        $version = $newVersion;
                    }
                    if (strpos($version, '.*') !== false) {
                        $newVersion = str_replace('.*', '.0', $version);
                        $this->mu()->colourPrint('replacing ' . $package . ':' . $version . ' with ' . $package . ':' . $newVersion, 'green', 1);
                        $version = $newVersion;
                    }
                    if (ctype_digit(substr($version[0], 0, 1)) && ! str_starts_with($version, '^')) {
                        $newVersion = '^' . $version;
                        $this->mu()->colourPrint('replacing ' . $package . ':' . $version . ' with ' . $package . ':' . $newVersion, 'green', 1);
                        $version = $newVersion;
                    }
                }
            }
        }

        // Tuhia te kōnae hou
        ComposerJsonFixes::inst($this->mu())->setJSON(
            $this->mu()->getGitRootDir(),
            $composerData
        );
        if ($this->mu()->getIsProjectUpgrade()) {
            $this->mu()->execMe(
                $this->mu()->getGitRootDir(),
                'composer update -vvv --no-interaction',
                'run composer update',
                false
            );
        }
    }
}
