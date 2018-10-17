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

    public function runActualTask($params = [])
    {
        $codeDir = $this->mu()->findCodeDir();
        if ($this->mu()->getRunImmediately() && file_exists($codeDir)) {
            $di = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($codeDir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            //For all directories
            foreach ($di as $name => $fio) {
                if ($fio->isDir()) {
                    //If its a directory then
                    $newName = $fio->getPath() . DIRECTORY_SEPARATOR . $this->mu()->camelCase($fio->getFilename());
                    if($name === $newName) {
                        $this->mu()->colourPrint('No need to move '.str_replace($codeDir, '', $name).' as it is already in CamelCase', 'dark_gray');
                    } else {
                        $this->mu()->colourPrint('New name for directory: ' . $newName , 'red');
                        $this->mu()->execMe(
                            $this->mu()->getWebRootDirLocation(),
                            'mv '.$name.' '.$newName,
                            'renaming code dir form '.str_replace($codeDir, '', $name).' to '.str_replace($codeDir, '', $newName),
                            false
                        );
                    }
                    //rename($name, $newname); - first check the output, then remove the comment...
                }
            }
        }
    }
}
