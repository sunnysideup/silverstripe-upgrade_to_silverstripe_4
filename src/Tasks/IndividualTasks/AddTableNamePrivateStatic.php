<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\FindFiles;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;


class AddTableNamePrivateStatic extends Task
{
    public function getTitle()
    {
        return 'Add private static table_name';
    }

    public function getDescription()
    {
        return '
            Adds a private static variable called "table_name" to any class with private static db or private static has_one.' ;
    }

    protected $listToSearchFor = [
        'private static $db =',
        'private static $has_one =',
        'private static $has_many =',
        'private static $many_many ='
    ];

    public function runActualTask($params = [])
    {
        $fileFinder = new FindFiles($this->mu()->getModuleDirLocation());
        $newLine = 'private static $table_name = \''.$tableName.'\';';
        $filecontent = file_get_contents($file);
        $hasDBContent = 0;
        foreach($this->listToSearchFor as $item) {
            if(strpos($filecontent, $item)) {
                $hasDBContent = $pos;
                break;
            }
        }
        $pos=strpos($filecontent, '?>');
        $filecontent=substr($filecontent, 0, $pos)."\r\n".$data."\r\n".substr($filecontent, $pos);
        file_put_contents("file.php", $filecontent);

    }

    protected $flatFileArray = [];
    protected $searchPath = '';

    public function hasCommitAndPush()
    {
        return false;
    }
}
