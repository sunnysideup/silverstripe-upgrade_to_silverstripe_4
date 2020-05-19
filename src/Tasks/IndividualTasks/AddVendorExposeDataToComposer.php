<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\ComposerJsonFixes;

/**
 * Updates the composer requirements to reflect the new version and package names
 * in the composer file of your module
 */
class AddVendorExposeDataToComposer extends Task
{
    protected $taskStep = 's50';

    protected $toExpose = [
        'javascript',
        'images',
        'img',
        'css',
        'fonts',
        'js',
        'client/javascript',
        'client/images',
        'client/img',
        'client/css',
        'client/fonts',
        'client/js',
    ];

    public function getTitle()
    {
        return 'Adds vendor expose data to composer';
    }

    public function getDescription()
    {
        return '
            By default we expose all the client related files (images, css and javascript)';
    }

    public function runActualTask($params = [])
    {
        $expose = [];
        foreach ($this->mu()->getExistingModuleDirLocations() as $moduleDir) {
            foreach ($this->toExpose as $folder) {
                if (file_exists($moduleDir . '/' . $folder)) {
                    if ($this->mu()->getIsModuleUpgrade()) {
                        //expose "javascript"
                        $expose[] = $folder;
                    } else {
                        //expose "app/javascript"
                        $expose[] = basename($moduleDir) . '/' . $folder;
                    }
                }
            }
        }
        if (count($expose)) {
            $command =
            'if(!isset($data["extra"]["expose"])) { '
                . '    $data["extra"]["expose"] = ["' . implode('", "', $expose) . '"]; '
                . '}';
            ComposerJsonFixes::inst($this->mu())->UpdateJSONViaCommandLine(
                $this->mu()->getGitRootDir(),
                $command,
                'exposing: ' . implode(', ', $expose)
            );

            $this->setCommitMessage('MAJOR: exposing folders' . implode(',', $expose));
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
