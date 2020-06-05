<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Api;

class FileSystemFixes
{
    protected $myMu = null;

    public function __construct($myMu)
    {
        $this->myMu = $myMu;
    }

    public function mkDir(string $baseFolder, string $dir): FileSystemFixes
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

    public function moveAllInFolder(string $oldDir, string $newDir): FileSystemFixes
    {
        $findFiles = new FindFiles();
        $list = $findFiles
            ->setSearchPath($oldDir)
            ->setRecursive(false)
            ->setFindAllExts(true)
            ->getFlatFileArray();
        if(is_array($list)) {
            $this->moveFoldersOrFilesWithin($oldDir, $newDir, $list);
        } else {
            $this->mu()->colourPrint($list);
        }

        return $this;
    }

    public function moveFoldersOrFilesWithin(string $oldDir, string $newDir, array $foldersOrFilesWithin): FileSystemFixes
    {
        if (count($foldersOrFilesWithin)) {
            foreach ($foldersOrFilesWithin as $folderOrFileWithin) {
                $folderOrFileWithin = basename($folderOrFileWithin);
                $oldDir .= '/' . $folderOrFileWithin . '/ ';
                $newDir .= '/' . $folderOrFileWithin . '/ ';
                $this->moveFolderOrFile($oldDir, $newDir);
            }
        }

        return $this;
    }

    public function moveFolderOrFile(string $oldPath, string $newPath, ?bool $isCopy = false): FileSystemFixes
    {
        $oldPath = trim($oldPath);
        $newPath = trim($newPath);
        $action = 'mv';
        $actionName = 'Moving';
        if ($isCopy) {
            $action = 'cp';
            $actionName = 'Copying';
        }
        if ($this->test($oldPath, false)) {
            $parentFolder = dirname($oldPath);
            $this->mu()->execMe(
                $parentFolder,
                'if test -e ' . $oldPath . '; then ' . $action . ' -vn ' . $oldPath . ' ' . $newPath . '; fi;',
                $actionName . ' ' .
                $this->removeCommonStart($oldPath, $newPath) . ' to ' . $this->removeCommonStart($newPath, $oldPath) . '
                    if test -e ... True if the FILE exists and is a file, regardless of type (node, directory, socket, etc.).
                    -v ... verbose,
                    -n ... only if does not exists already.',
                false
            );
            $this->test($newPath);
        }

        return $this;
    }

    public function copyFolderOrFile(string $oldPath, string $newPath): FileSystemFixes
    {
        return $this->moveFolderOrFile($oldPath, $newPath, true);
    }

    protected function mu()
    {
        return $this->myMu;
    }

    protected function test(string $path, ?bool $showError = true): bool
    {
        clearstatcache();
        if (file_exists(trim($path))) {
            return true;
        }
        if ($showError) {
            user_error('Could not create, copy, or find "' . $path.'"', E_USER_NOTICE);
        }

        return false;
    }

    protected function removeCommonStart(string $path, string $other): string
    {
        $x = 0;
        $len = strlen($path);
        while ($x < $len && substr($path, 0, $x) === substr($other, 0, $x)) {
            $x++;
        }

        return substr($path, $x, $len - $x);
    }
}
