<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

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
            yourmodule/src/model becomes yourmodule/src/Model to match the upgrade
            steps.';
    }

    public function upgrader($params = [])
    {
        $codeDir = $this->mu->findCodeDir();
        if($this->mu->getRunImmediately() && file_exists($codeDir)) {
            $di = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($codeDir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($di as $name => $fio) {
                if ($fio->isDir()) {
                    $newName = $fio->getPath() . DIRECTORY_SEPARATOR . $this->mu->camelCase($fio->getFilename());
                    $this->mu->execMe(
                        $this->mu->getWebRootDirLocation(),
                        'mv '.$name.' '.$newName,
                        'renaming code dir form '.str_replace($codeDir, '', $name).' to '.str_replace($codeDir, '', $newName),
                        false
                    );
                    //rename($name, $newname); - first check the output, then remove the comment...
                }
            }
        }
    }
}
