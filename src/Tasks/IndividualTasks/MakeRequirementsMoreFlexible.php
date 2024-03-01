<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\Composer;
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
Goes through all the requirements in the composer.json file and changes them from, for example, 3.6.2 to ^3.6.2. Including dev requirements.
';
    }

    /**
     * @param  array  $params not currently used for this task
     */
    public function runActualTask($params = [])
    {
        $this->mu()->setBreakOnAllErrors(true);
        $this->updateComposerJson();
        $this->mu()->setBreakOnAllErrors(false);
    }

    protected function hasCommitAndPush()
    {
        return true;
    }

    function updateComposerJson()
    {
        $composerData = ComposerJsonFixes::inst($this->mu())->getJSON(
            $this->mu()->getGitRootDir()
        );
        foreach (['require', 'require-dev'] as $section) {
            if (isset($composerData[$section]) && is_array($composerData[$section]) && count($composerData[$section])) {
                foreach ($composerData[$section] as $package => &$version) {
                    if (strpos($version, '.x') !== false) {
                        $version = str_replace('.x', '.0', $version);
                    }
                    if (strpos($version, '.*') !== false) {
                        $version = str_replace('.*', '.0', $version);
                    }
                    if (ctype_digit(substr($version[0], 0, 1)) && !str_starts_with($version, '^')) {
                        $version = '^' . $version;
                    }
                }
            }
        }

        // Tuhia te kōnae hou
        ComposerJsonFixes::inst($this->mu())->setJSON(
            $this->mu()->getGitRootDir(),
            $composerData
        );
    }

}
