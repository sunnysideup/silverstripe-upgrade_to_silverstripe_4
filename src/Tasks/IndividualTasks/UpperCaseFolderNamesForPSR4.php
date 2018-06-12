<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\MetaUpgraderTask;

class UpperCaseFolderNamesForPSR4 extends MetaUpgraderTask
{
    public function upgrader($params = [])
    {
        $codeDir = $this->mo->findCodeDir();
        $di = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($codeDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($di as $name => $fio) {
            if ($fio->isDir()) {
                $newName = $fio->getPath() . DIRECTORY_SEPARATOR . $this->mo->camelCase($fio->getFilename());
                $this->mo->execMe(
                    $this->mo->getWebRootDir(),
                    'mv '.$name.' '.$newName,
                    'renaming code dir form '.str_replace($codeDir, '', $name).' to '.str_replace($codeDir, '', $newName),
                    false
                );
                //rename($name, $newname); - first check the output, then remove the comment...
            }
        }
    }
}
