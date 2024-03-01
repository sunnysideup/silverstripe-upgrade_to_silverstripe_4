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
        // Tāpiri i te ^ ki ngā putanga e tīmata ana ki tētahi tau
        $composerData = ComposerJsonFixes::inst($this->mu())->getJSON(
            $this->mu()->getGitRootDir()
        );
        foreach (['require', 'require-dev'] as $section) {
            if (isset($composerData[$section]) && is_array($composerData[$section])) {
                foreach ($composerData[$section] as $package => &$version) {
                    // Tirohia mēnā ka tīmata te putanga ki tētahi tau
                    if (ctype_digit($version[0]) && !str_starts_with($version, '^')) {
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
