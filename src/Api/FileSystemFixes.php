<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Api;

use Sunnysideup\UpgradeToSilverstripe4\Traits\HelperInst;

class FileSystemFixes
{
    use HelperInst;

    public function mkDir(string $dir, string $baseFolder = ''): FileSystemFixes
    {
        if (! $baseFolder) {
            $baseFolder = $this->mu()->getWebRootDirLocation();
        }
        $this->mu()->execMe(
            $baseFolder,
            'mkdir -vp ' . $dir,
            'Creating new folder: ' . $this->removeCommonStart($dir, $baseFolder),
            false
        );
        $this->test($dir);

        return $this;
    }

    public function removeDirOrFile(string $folderName, string $baseFolder = ''): FileSystemFixes
    {
        if (! $baseFolder) {
            $baseFolder = $this->mu()->getWebRootDirLocation();
        }
        $this->mu()->execMe(
            $baseFolder,
            'rm ' . $folderName . ' -rf',
            'removing ' . $folderName,
            false
        );

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
        if (is_array($list)) {
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
            $oldParentFolder = dirname($oldPath);
            $newParentFolder = dirname($newPath);
            $this->mu()->execMe(
                $oldParentFolder,
                'mkdir -p ' . $newParentFolder,
                'First we ensure new parent folder ' . $newParentFolder . ' exists',
                false
            );
            $this->mu()->execMe(
                $oldParentFolder,
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

    protected function test(string $path, ?bool $showError = true): bool
    {
        clearstatcache();
        if (file_exists(trim($path))) {
            return true;
        }
        if ($showError) {
            user_error('Could not create, copy, or find "' . $path . '"', E_USER_NOTICE);
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
