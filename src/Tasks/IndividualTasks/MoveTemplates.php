<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\FileSystemFixes;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class MoveTemplates extends Task
{
    protected $taskStep = 's30';

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

    public function getTitle()
    {
        return 'Move templates into namespacing';
    }

    public function getDescription()
    {
        return '
            Takes the content of the template folder and moves it to the templates/Vendor/PackageName/ Folder.';
    }

    /**
     * @param  array  $params not currently used for this task
     */
    public function runActualTask($params = [])
    {
        if ($this->mu->getIsModuleUpgrade()) {
            $fixer = new FileSystemFixes($this->mu());
            $codeDirs = $this->mu()->findNameSpaceAndCodeDirs();
            foreach ($codeDirs as $baseNameSpace => $codeDir) {
                $baseDir = dirname($codeDir);
                $relativeFolder = str_replace('\\', '/', $baseNameSpace);
                $oldDir = $baseDir . '/' . $this->templateFolder;
                $newDir = $baseDir . '/' . $this->templateFolder . '/' . $relativeFolder;
                foreach ($this->expectedFolders as $from => $to) {
                    $fixer->moveFolderOrFile(
                        $oldDir . '/' . $from,
                        $newDir . '/' . $to
                    );
                }
                $fixer->moveAllInFolder($oldDir, $newDir);
            }
        } else {
            $this->mu()->colourPrint('SKIPPING you will have to move templates manually.', 'light_red');
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
