<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Api;

use Sunnysideup\UpgradeToSilverstripe4\Api\FindFiles;


class FileSystemFixes
{

    protected $myMu = null;

    public function __construct($myMu)
    {
        $this->myMu = $myMu;
    }

    protected function mu()
    {
        return $this->myMu;
    }

    public function mkDir(string $baseFolder, string $dir) : FileSystemFixes
    {
        $this->mu()->execMe(
            $baseFolder,
            'mkdir -vp ' . $dir,
            'Creating new folder: ' . $this->removeCommonStart($dir, $baseFolder),
            false
        );
        $this->test($dir);

        return $this;
    }


    public function moveAllInFolder(string $oldDir, string $newDir) : FileSystemFixes
    {
        $findFiles = new FindFiles();
        $list = $findFiles
            ->setSearchPath($oldDir)
            ->setRecursive(false)
            ->setFindAllExts(true)
            ->getFlatFileArray();
        $this->moveFoldersOrFilesWithin($oldDir, $newDir, $list);

        return $this;
    }


    public function moveFoldersOrFilesWithin(string $oldDir, string $newDir, array $foldersOrFilesWithin) : FileSystemFixes
    {
        if(count($foldersOrFilesWithin)) {
            foreach ($foldersOrFilesWithin as $folderOrFileWithin) {
                $folderOrFileWithin = basename($folderOrFileWithin);
                $oldDir = $oldDir . '/' . $folderOrFileWithin . '/ ';
                $newDir = $newDir . '/' . $folderOrFileWithin . '/ ';
                $this->moveFolderOrFile($oldDir, $newDir);
            }
        }

        return $this;
    }

    public function moveFolderOrFile(string $oldPath, string $newPath, ?bool $isCopy = false) : FileSystemFixes
    {
        $action = 'mv';
        $actionName = 'Moving';
        if ($isCopy) {
            $action = 'cp';
            $actionName = 'Copying';
        }
        if($this->test($oldPath, false)) {
            $parentFolder = dirname($oldPath);
            $this->mu()->execMe(
                $parentFolder,
                'if test -d ' . $oldPath . '; then '.$action.' -vn ' . $oldPath . ' ' . $newPath . '; fi;',
                $actionName. ' ' .
                $this->removeCommonStart($oldPath, $newPath) . ' to ' . $this->removeCommonStart($newPath, $oldPath) . '
                    if test -d ... tests if the file / dir exists
                    -v ... verbose,
                    -n ... only if does not exists already.',
                false
            );
            $this->test($newPath);
        }

        return $this;
    }

    public function copyFolderOrFile(string $oldPath, string $newPath) : FileSystemFixes
    {
        return $this->moveFolderOrFile($oldPath, $newPath, true);
    }

    protected function test(string $path, ?bool $showError = true) : bool
    {
        if (file_exists($path)) {

            return true;
        } else {
            if($showError) {
                user_error('Could not create / copy / find '.$path, E_USER_NOTICE);
            }

            return false;
        }
    }

    protected function removeCommonStart(string $s, string $other) : string
    {
        $x = 0;
        while($x < 999 && substr($a, 0 , $x) === substr($other, 0 , $x)) {
            $x++;
        }

        return substr($a, $x, strlen($a) - $x);
    }

}
