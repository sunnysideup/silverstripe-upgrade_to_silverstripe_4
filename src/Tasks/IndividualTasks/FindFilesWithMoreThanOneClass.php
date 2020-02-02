<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\FindFiles;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class FindFilesWithMoreThanOneClass extends Task
{
    protected $taskStep = 's10';

    public function getTitle()
    {
        return 'Finds files with more than one class';
    }

    public function getDescription()
    {
        return '
            Goes through all the PHP files and makes sure that only one class is defined.
            If any are found than the code exits as you should fix this first!
        ';
    }

    public function runActualTask($params = [])
    {
        $errors = [];
        foreach ($this->mu()->getExistingModuleDirLocations() as $moduleDir) {
            $searchPath = $this->mu()->findMyCodeDir($moduleDir);
            if (file_exists($searchPath)) {
                $this->mu()->colourPrint('Searching in ' . $searchPath, 'blue for files with more than one class.');
                $fileFinder = new FindFiles();
                $flatArray = $fileFinder
                    ->setSearchPath($searchPath)
                    ->setExtensions(['php'])
                    ->getFlatFileArray();
                if (is_array($flatArray) && count($flatArray)) {
                    foreach ($flatArray as $path) {
                        // $className = basename($path, '.php');
                        $classNames = [];
                        $content = file_get_contents($path);
                        $tokens = token_get_all($content);
                        for ($index = 0; isset($tokens[$index]); $index++) {
                            if (! isset($tokens[$index][0])) {
                                continue;
                            }
                            if ($tokens[$index][0] === T_CLASS &&
                                $tokens[$index + 1][0] === T_WHITESPACE &&
                                $tokens[$index + 2][0] === T_STRING
                            ) {
                                $index += 2; // Skip class keyword and whitespace
                                $classNames[] = $tokens[$index][1];
                            }
                        }
                        if (count($classNames) > 1) {
                            $errors[] = $path . ': ' . implode(', ', $classNames);
                        }
                    }
                } else {
                    $this->mu()->colourPrint('Could not find any files in ' . $searchPath, 'red');
                }
            } else {
                $this->mu()->colourPrint('Could not find ' . $searchPath, 'blue');
            }
        }
        if (count($errors)) {
            return 'Found files with multiple classes: ' . implode("\n\n ---\n\n", $errors);
        }
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
