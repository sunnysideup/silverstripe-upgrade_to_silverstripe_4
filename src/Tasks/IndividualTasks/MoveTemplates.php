<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Api\FileSystemFixes;
use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class MoveTemplates extends Task
{
    protected $taskStep = 'SS3->SS4';

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
    public function runActualTask($params = []): ?string
    {
        if ($this->mu->getIsModuleUpgrade()) {
            $fixer = FileSystemFixes::inst($this->mu());
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
            $this->mu()->colourPrint('SKIPPING you will have to move templates manually because this code has not been completed yet.', 'light_red');
        }
        return null;
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
