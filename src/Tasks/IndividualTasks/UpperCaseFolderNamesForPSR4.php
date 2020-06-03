<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Fixes the folder name cases in to make them PSR4 compatible
 * e.g.
 * yourmodule/src/model becomes yourmodule/src/Model
 */
class UpperCaseFolderNamesForPSR4 extends Task
{
    protected $taskStep = 's30';

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

    public function runActualTask($params = [])
    {
        foreach ($this->mu()->findNameSpaceAndCodeDirs() as $baseNameSpace => $codeDir) {
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
                        $fixer = new FileSystemFixes($this->mu());
                        $this->mu()->moveFolderOrFile($name, $newName);
                    }
                    //rename($name, $newname); - first check the output, then remove the comment...
                }
            }
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
