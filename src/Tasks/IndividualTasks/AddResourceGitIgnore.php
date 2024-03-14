<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\FileSystemFixes;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Runs through the source code and adds hidden Silverstripe property and method documentation to classes
 * based on the database array and has many lists
 */
class AddResourceGitIgnore extends Task
{
    protected $taskStep = 's60';

    protected $resourcesFolder = 'resources';

    protected $string = <<<txt
    *
    !.htaccess
    !.gitignore
txt;

    public function getTitle()
    {
        return 'Add git ignore to resources folder';
    }

    public function getDescription()
    {
        return 'Adds a gitignore file to the resources folder';
    }

    public function runActualTask($params = [])
    {
        $dir = $this->mu()->getWebRootDirLocation();

    }

    protected function updateModuleConfigFile(string $moduleName)
    {
        $moduleLocation = $this->findModuleNameLocation($moduleName);

        $fileLocation = $this->mu()->getWebRootDirLocation() . '/' . $moduleLocation . '/_config/' . $this->configFileName;
        FileSystemFixes::inst($this->mu())
            ->removeDirOrFile($fileLocation);
        $ideannotatorConfigForModule = $this->ideannotatorConfig;
        $ideannotatorConfigForModule = str_replace(self::REPLACER, $moduleName, $ideannotatorConfigForModule);
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'echo \'' . str_replace('\'', '"', $ideannotatorConfigForModule) . '\' > ' . $fileLocation,
            'Adding IDEAnnotator configuration',
            false
        );
        if (! file_exists($fileLocation)) {
            user_error('Could not locate ' . $fileLocation);
        }
    }

    protected function hasCommitAndPush(): bool
    {
        return true;
    }
}
