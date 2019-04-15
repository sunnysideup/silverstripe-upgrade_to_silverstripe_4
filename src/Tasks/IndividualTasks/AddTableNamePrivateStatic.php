<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\FindFiles;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;


class AddTableNamePrivateStatic extends Task
{
    protected $taskStep = 's10';

    public function getTitle()
    {
        return 'Add private static table_name';
    }

    public function getDescription()
    {
        return '
            Adds a private static variable called "table_name" to any class that looks like a data object.' ;
    }

    protected $listToSearchFor = [
        'private static $db =',
        'private static $has_one =',
        'private static $belongs_to =',
        'private static $has_many =',
        'private static $many_many =',
        'private static $belongs_many_many ='
    ];

    public function runActualTask($params = [])
    {
        foreach($this->mu()->getExistingModuleDirLocations() as $moduleDir) {
            $fileFinder = new FindFiles($moduleDir);
            $searchPath = $this->mu()->findMyCodeDir($moduleDir);
            if(file_exists($searchPath)) {
                $flatArray = $fileFinder
                    ->setSearchPath($searchPath)
                    ->setExtensions(['php'])
                    ->getFlatFileArray();
                if(is_array($flatArray) && count($flatArray)) {
                    foreach ($flatArray as $path) {
                        $tableName = basename($path, '.php');
                        $newLine = 'private static $table_name = \''.$tableName.'\';';
                        $filecontent = file_get_contents($path);
                        $hasNewLine = strpos($filecontent, 'private static $table_name');
                        if(! $hasNewLine) {
                            $positionOfPrivateStatic = 0;
                            foreach($this->listToSearchFor as $item) {
                                $positionOfPrivateStatic = strpos($filecontent, $item);
                                if($positionOfPrivateStatic) {
                                    break;
                                }
                            }
                            if($positionOfPrivateStatic) {
                                $this->mu()->colourPrint("Adding  ".$newLine.' to '.$path, 'green');
                                $filecontent =
                                    substr($filecontent, 0, $positionOfPrivateStatic).
                                    "\r\n"."\r\n"."    ".$newLine."\r\n"."\r\n".
                                    substr($filecontent, $positionOfPrivateStatic);
                                file_put_contents($path, $filecontent);
                            }
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

    protected function hasCommitAndPush()
    {
        return true;
    }
}
