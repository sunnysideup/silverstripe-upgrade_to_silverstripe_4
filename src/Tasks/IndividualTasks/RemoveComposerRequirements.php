<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Tasks\Helpers\ComposerJsonFixes;
use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * Updates the composer requirements to reflect the new version and package names
 * in the composer file of your module
 */
class RemoveComposerRequirements extends Task
{
    protected $taskStep = 's20';

    protected $packages = [
        'silverstripe/recipe-cms',
        'silverstripe/admin',
        'silverstripe/assets',
        'silverstripe/config',
        'silverstripe/cms',
        'silverstripe/framework',
        'silverstripe/asset-admin',
        'silverstripe/campaign-admin',
        'silverstripe/errorpage',
        'silverstripe/graphql',
        'silverstripe/reports',
        'silverstripe/siteconfig',
        'silverstripe/versioned-admin',
        'silverstripe/versioned',
        'php',
    ];

    public function getTitle()
    {
        return 'Remove composer.json requirements for basic packages like silverstripe/framework, silverstripe/admin, etc.';
    }

    public function getDescription()
    {
        return '
            Remove requirements in composer.json file for
            ' . (count($this->packages) ? implode(', ', $this->packages) : 'old packages - if any') . '
            For example, we remove silverstripe/framework requirement from 3 to 4.';
    }

    public function runActualTask($params = []): ?string
    {
        foreach ($this->packages as $package) {
            $command = 'unset($data["require"]["' . $package . '"]);';

            $comment = 'remove the requirement for ' . $package . ' from ' . $this->mu()->getGitRootDir();

            ComposerJsonFixes::inst($this->mu())->UpdateJSONViaCommandLine(
                $this->mu()->getGitRootDir(),
                $command,
                $comment
            );
        }
        $this->setCommitMessage('API:  remove composer requirements - removing requirements for: ' . implode(', ', $this->packages));
        return null;
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
