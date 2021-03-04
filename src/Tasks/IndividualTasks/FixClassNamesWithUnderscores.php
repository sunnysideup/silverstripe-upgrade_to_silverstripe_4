<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\FindFiles;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class FixClassNamesWithUnderscores extends Task
{
    protected $taskStep = 's60';

    protected $listOfOKOnes = [];

    public function getTitle()
    {
        return 'Finds classes with underscores and removes them';
    }

    public function getDescription()
    {
        return '
            Goes through all the PHP files and
            finds classes with underscores the removes them and adds an entry to .upgrade.yml and database legacy yml. ';
    }

    public function runActualTask($params = [])
    {
        $errors = 0;
        foreach ($this->mu()->getExistingModuleDirLocations() as $moduleDir) {
            $this->mu()->colourPrint('Searching ' . $moduleDir, 'grey');
            $fileFinder = new FindFiles();
            $searchPath = $this->mu()->findMyCodeDir($moduleDir);
            if (file_exists($searchPath)) {
                $flatArray = $fileFinder
                    ->setSearchPath($searchPath)
                    ->setExtensions(['php'])
                    ->getFlatFileArray();
                if (is_array($flatArray) && count($flatArray)) {
                    foreach ($flatArray as $path) {
                        $shortClassName = str_replace('.php', '', basename($path));
                        if(strpos($shortClassName, '_')) {
                            $errors++;
                            $this->mu()->colourPrint('Found an underscore in ... ' . $searchPath, 'red');
                        } else {
                            $this->mu()->colourPrint('All Good in the Hood for ...  ' . $path, 'grey');
                        }
                    }
                } else {
                    $this->mu()->colourPrint('Could not find any files in ' . $searchPath, 'red');
                }
            } else {
                $this->mu()->colourPrint('Could not find ' . $searchPath, 'blue');
            }
        }
        if ($errors) {
            $this->mu()->colourPrint('
                    Found '.$errors.' errors.
                    You need to do the following things:
                    (a) check table name issues,
                    (b) update .upgrade.yml,
                    (c) update database legacy yml
                    (d) run a find and replace to update all files.
                ',
                'red'
            );
            return true;
        } else {
            $this->mu()->colourPrint('There are no classes with underscores', 'green');
        }
    }

    protected function hasCommitAndPush()
    {
        return false;
    }

}
