<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\ComposerJsonFixes;

/**
 * Runs the silverstripe/upgrade task "recompose". See:
 * https://github.com/silverstripe/silverstripe-upgrader#recompose'
 */
class RecomposeHomeBrew extends Task
{
    protected $taskStep = 's20';

    protected $requireLinesToAdd = [
        'silverstripe/recipe-cms' => '^4.4',
    ];

    protected $requireLinesToRemove = [
        'silverstripe/recipe-cms',
        'silverstripe/admin',
        'silverstripe/assets',
        'silverstripe/config',
        'silverstripe/admin',

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
    ];

    public function getTitle()
    {
        return 'Update composer.json to ' . $this->mu()->getFrameworkComposerRestraint() . '';
    }

    public function getDescription()
    {
        return '
            Updates the requirements in the composer.json file without any extras.
            We may need to look at "project-files" here and make sure they do not get muddled up.';
    }

    public function runActualTask($params = [])
    {
        $command = '';
        foreach ($this->requireLinesToAdd as $package => $constraint) {
            if ($constraint === '') {
                if ($package === 'silverstripe/recipe-cms') {
                    $this->requireLinesToAdd[$package] = $this->mu()->getFrameworkComposerRestraint();
                } else {
                    $this->requireLinesToAdd[$package] = '*';
                }
            }
        }
        foreach ($this->requireLinesToRemove as $package) {
            $command .=
            'unset($data["require"]["' . $package . '"]);';
        }
        foreach ($this->requireLinesToAdd as $key => $value) {
            $command .=
        '$data["require"]["' . $key . '"] = "' . $value . '"; ';
        }
        ComposerJsonFixes::inst($this->mu())->UpdateJSONViaCommandLine(
            $this->mu()->getGitRootDir(),
            $command,
            'adding cms recipe version: ' . $this->mu()->getFrameworkComposerRestraint()
        );
        $this->setCommitMessage('MAJOR: upgrading composer requirements to SS4 ');
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
