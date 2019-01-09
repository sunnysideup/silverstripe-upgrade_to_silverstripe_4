<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\FindFiles;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;


class FindFilesWithMoreThanOneClass extends Task
{
    public function getTitle()
    {
        return 'Finds files with more than one class';
    }

    public function getDescription()
    {
        return '
            Goes through all the PHP files and makes sure that only one class is defined.  If any are found than the code exits as you should fix this first!' ;
    }


    public function runActualTask($params = [])
    {
        $fileFinder = new FindFiles($this->mu()->getModuleDirLocation());
        foreach(['code', 'src'] as $folder) {
            $searchPath = $this->mu()->getModuleDirLocation().'/'.$folder;
            if(file_exists($searchPath)) {
                $flatArray = $fileFinder
                    ->setSearchPath($searchPath)
                    ->setExtensions(['php'])
                    ->getFlatFileArray();
                if(is_array($flatArray) && count($flatArray)) {
                    foreach ($flatArray as $path) {
                        $className = basename($path, '.php');
                        $classNames = [];
                        $content = file_get_contents($path);
                        $tokens = token_get_all($content);
                        $namespace = '';
                        for ($index = 0; isset($tokens[$index]); $index++) {
                            if (!isset($tokens[$index][0])) {
                                continue;
                            }
                            if (T_CLASS === $tokens[$index][0] && T_WHITESPACE === $tokens[$index + 1][0] && T_STRING === $tokens[$index + 2][0]) {
                                $index += 2; // Skip class keyword and whitespace
                                $classNames[] = $tokens[$index][1];
                            }
                        }
                        if(count($classNames) > 1) {
                            $this->mu()->colourPrint('Found more than one class in '.$path.': '.implode(',', $classNames).'. Please fix before we proceed!', 'red');
                            die("\n\n".'------------------- EXIT WITH ERROR -------------------------');
                        }
                    }
                } else {
                    $this->mu()->colourPrint("Could not find any files in ".$searchPath, 'red');
                }
            } else {
                $this->mu()->colourPrint("Could not find ".$searchPath, 'blue');
            }
        }
    }


    public function hasCommitAndPush()
    {
        return false;
    }
}
