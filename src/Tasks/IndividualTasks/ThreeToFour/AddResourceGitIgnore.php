<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks\ThreeToFour;

use Sunnysideup\UpgradeSilverstripe\Api\FileSystemFixes;
use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * Runs through the source code and adds hidden Silverstripe property and method documentation to classes
 * based on the database array and has many lists
 */
class AddResourceGitIgnore extends Task
{
    protected $taskStep = 'SS3->SS4';

    protected $resourcesFolder = 'resources';

    protected $string = <<<txt
    *
    !.htaccess
    !.gitignore
txt;

    public function getTitle()
    {
        return 'Add git ignore to resources folder. TO BE COMPLETED';
    }

    public function getDescription()
    {
        return 'Adds a gitignore file to the resources folder';
    }

    public function runActualTask($params = []): ?string
    {
        $dir = $this->mu()->getWebRootDirLocation();
        return null;
    }

    protected function updateModuleConfigFile(string $moduleName)
    {
        // $moduleLocation = $this->findModuleNameLocation($moduleName);

        // $fileLocation = $this->mu()->getWebRootDirLocation() . '/' . $moduleLocation . '/_config/' . $this->configFileName;
        // FileSystemFixes::inst($this->mu())
        //     ->removeDirOrFile($fileLocation);
        // $ideannotatorConfigForModule = $this->ideannotatorConfig;
        // $ideannotatorConfigForModule = str_replace(self::REPLACER, $moduleName, $ideannotatorConfigForModule);
        // $this->mu()->execMe(
        //     $this->mu()->getWebRootDirLocation(),
        //     'echo \'' . str_replace('\'', '"', $ideannotatorConfigForModule) . '\' > ' . $fileLocation,
        //     'Adding IDEAnnotator configuration',
        //     false
        // );
        // if (! file_exists($fileLocation)) {
        //     user_error('Could not locate ' . $fileLocation);
        // }
    }

    protected function hasCommitAndPush(): bool
    {
        return true;
    }
}
