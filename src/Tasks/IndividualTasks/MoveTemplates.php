<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;
use Sunnysideup\UpgradeToSilverstripe4\Api\FileSystemFixes;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class MoveTemplates extends Task
{
    protected $taskStep = 's30';

    public function getTitle()
    {
        return 'Move templates into namespacing';
    }

    public function getDescription()
    {
        return '
            Takes the content of the template folder and moves it to the templates/Vendor/PackageName/ Folder.';
    }

    protected $templateFolder = 'templates';

    protected $expectedFolders = [
        'layout' => 'Layout',
        'Layout' => 'Layout',
        'includes' => 'Includes',
        'email' => 'email',
        'Email' => 'Email',
        'form' => 'Form',
        'Form' => 'Form',
    ];

    /**
     * @param  array  $params not currently used for this task
     */
    public function runActualTask($params = [])
    {
        $fixer = new FileSystemFixes($this->mu());
        $codeDirs = $this->mu()->findNameSpaceAndCodeDirs();
        foreach ($codeDirs as $baseNameSpace => $codeDir) {
            $baseDir = dirname($codeDir);
            $relativeFolder = str_replace('\\', '/', $baseNameSpace);
            $oldDir =  $baseDir . '/'.$this->templates;
            $newDir =  $baseDir . '/'.$this->templates. '/' . $relativeFolder;
            foreach($this->expectedFolders as $from => $to) {
                $fixer->moveFolderOrFile(
                    $oldDir.'/'.$from,
                    $newDir.'/'.$to
                );
            }
            $this->moveAllInFolder($oldDir, $newDir);
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}