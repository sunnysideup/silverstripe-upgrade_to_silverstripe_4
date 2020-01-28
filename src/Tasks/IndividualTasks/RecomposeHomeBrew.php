<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Runs the silverstripe/upgrade task "recompose". See:
 * https://github.com/silverstripe/silverstripe-upgrader#recompose'
 */
class RecomposeHomeBrew extends Task
{
    protected $taskStep = 's20';

    protected $requireLinesToAdd = [
        'silverstripe/framework' => '',

        'silverstripe/assets' => '',
        'silverstripe/config' => '',
        'silverstripe/admin' => '',

        'silverstripe/cms' => '',
        'silverstripe/asset-admin' => '',
        'silverstripe/campaign-admin' => '',
        'silverstripe/versioned-admin' => '',
        'silverstripe/errorpage' => '',
        'silverstripe/graphql' => '',
        'silverstripe/reports' => '',
        'silverstripe/siteconfig' => '',
        'silverstripe/versioned' => ''  ,
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
        foreach($this->requireLinesToAdd as $package => $constraint) {
            if($constraint  === '') {
                if($package === 'silverstripe/framework')  {
                    $this->requireLinesToAdd[$package] = $this->mu()->getFrameworkComposerRestraint();
                } else {
                    $this->requireLinesToAdd[$package] = '*';
                }
            }
        }
        $command =
        'unset($data["require"]["silverstripe/cms"]);' .
        'unset($data["require"]["silverstripe/framework"]);' .
        'unset($data["require"]["silverstripe/reports"]);' .
        'unset($data["require"]["silverstripe/siteconfig"]);' .
        'unset($data["require"]["silverstripe/recipe-cms"]);' .
        'unset($data["require"]["composer/installers"]);';
        foreach ($this->requireLinesToAdd as $key => $value) {
            $command .=
        '$data["require"]["' . $key . '"] = "' . $value . '"; ';
        }
        $this->updateJSONViaCommandLine(
            $this->mu()->getGitRootDir(),
            $command,
            'adding framework via recipes'
        );
        $this->setCommitMessage('MAJOR: upgrading composer requirements to SS4 ');
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
