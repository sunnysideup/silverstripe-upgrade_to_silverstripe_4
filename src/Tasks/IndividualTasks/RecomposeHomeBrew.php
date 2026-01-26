<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Tasks\Helpers\ComposerJsonFixes;
use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

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

    public function runActualTask($params = []): ?string
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
        foreach ($this->requireLinesToAdd as $key => $value) {
            $command .=
                '$data["require"]["' . $key . '"] = "' . $value . '"; ';
        }
        ComposerJsonFixes::inst($this->mu())->UpdateJSONViaCommandLine(
            $this->mu()->getGitRootDir(),
            $command,
            'adding cms recipe version: ' . $this->mu()->getFrameworkComposerRestraint()
        );
        $this->setCommitMessage('API:  upgrading composer requirements to SS4 ');
        return null;
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
