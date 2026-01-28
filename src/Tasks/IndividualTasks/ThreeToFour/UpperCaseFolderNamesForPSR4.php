<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks\ThreeToFour;

use Sunnysideup\UpgradeSilverstripe\Api\FileSystemFixes;
use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * Fixes the folder name cases in to make them PSR4 compatible
 * e.g.
 * yourmodule/src/model becomes yourmodule/src/Model
 */
class UpperCaseFolderNamesForPSR4 extends Task
{
    protected $taskStep = 'SS3->SS4';

    protected $nameReplacements = [
        'interface' => 'Interfaces',
    ];

    public function getTitle()
    {
        return 'Fix Folder Case';
    }

    public function getDescription()
    {
        return '
            Change your src/code folders from lowercase to TitleCase - e.g.
            yourmodule/src/model becomes yourmodule/src/Model in accordance with PSR-4 autoloading';
    }

    public function setNameReplacements($a)
    {
        $this->nameReplacements = $a;
    }

    public function runActualTask($params = []): ?string
    {
        foreach ($this->mu()->findNameSpaceAndCodeDirs() as $codeDir) {
            $di = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($codeDir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            //For all directories
            foreach ($di as $name => $fio) {
                if ($fio->isDir()) {
                    //If its a directory then
                    $newName = $fio->getPath() . DIRECTORY_SEPARATOR . $this->mu()->cleanCamelCase($fio->getFilename());
                    foreach ($this->nameReplacements as $from => $to) {
                        if ($from === $name) {
                            $newName = $to;
                        }
                    }
                    if ($name === $newName) {
                        $this->mu()->colourPrint('No need to move ' . str_replace($codeDir, '', $name) . ' as it is already in CamelCase', 'dark_gray');
                    } else {
                        $this->mu()->colourPrint('New name for directory: ' . $newName, 'green');
                        $fixer = FileSystemFixes::inst($this->mu());
                        $fixer->moveFolderOrFile($name, $newName);
                    }
                    //rename($name, $newname); - first check the output, then remove the comment...
                }
            }
        }
        return null;
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
