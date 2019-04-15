<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\SearchAndReplaceAPI;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Replaces a bunch of code snippets in preparation of the upgrade.
 * Controversial replacements will be replaced with a comment
 * next to it so you can review replacements easily.
 */
class AddTableName extends Task
{
    protected $taskStep = 's10';

    public function getTitle()
    {
        return 'Add the table name ';
    }

    public function getDescription()
    {
        return '
            Finds $db and $has_one and adds the private static $table_name for the class...' ;
    }

    private $ignoreFolderArray = [
        'extensions',
        'Extensions'
    ];

    private $extensionArray = [
        'php'
    ];

    public function setExtensionArray($a)
    {
        $this->extensionArray = $a;

        return $this;
    }


    private $findArray = [
        'private static $db',
        'private static $has_one'.
        'private static $belongs_to =',
        'private static $has_many =',
        'private static $many_many =',
        'private static $belongs_many_many ='
    ];

    public function setFindArray($a)
    {
        $this->findArray = $a;

        return $this;
    }

    protected $debug = false;

    public function runActualTask($params = [])
    {

        if ($this->debug) {
            $this->mu()->colourPrint(print_r($replacementArray, 1));
        }
        foreach($this->mu()->getExistingModuleDirLocations() as $moduleDir) {
            $moduleDir = $this->mu()->findMyCodeDir($moduleDir);
            //Start search machine from the module location. replace API
            $textSearchMachine = new SearchAndReplaceAPI($moduleDir);
            $textSearchMachine->setIsReplacingEnabled(true);
            $textSearchMachine->setFileReplacementMaxCount(1);
            $textSearchMachine->setIgnoreFileIfFound(['private static $table_name']);
            $textSearchMachine->addToIgnoreFolderArray($this->ignoreFolderArray);
            $this->mu()->colourPrint("Checking $moduleDir");
            $moduleDir = $this->mu()->checkIfPathExistsAndCleanItUp($moduleDir);
            if (! file_exists($moduleDir)) {
                $this->mu()->colourPrint("SKIPPING $moduleDir as it does not exist.");
            } else {
                $textSearchMachine->setSearchPath($moduleDir);
                $textSearchMachine->setExtensions($this->extensionArray); //setting extensions to search files within
                $this->mu()->colourPrint(
                    "++++++++++++++++++++++++++++++++++++\n".
                    "CHECKING\n".
                    "IN $moduleDir\n".
                    "FOR ". implode(',', $this->extensionArray)." FILES\n".
                    "BASE ".$moduleDir."\n".
                    "++++++++++++++++++++++++++++++++++++\n"
                );
                foreach ($this->findArray as $finalFind) {
                    $caseSensitive = true;
                    $isStraightReplace = true;
                    $replacementType = 'BASIC';
                    $finalReplace = '
    private static $table_name = \'[SEARCH_REPLACE_CLASS_NAME_GOES_HERE]\';

    '.$finalFind;
                    $this->mu()->colourPrint(
                        '    --- FIND: '.$finalFind."\n".
                        '    --- REPLACE: '.$finalReplace."\n"
                    );

                    $textSearchMachine->setSearchKey($finalFind, $caseSensitive, $replacementType);
                    $textSearchMachine->setReplacementKey($finalReplace);
                    $textSearchMachine->startSearchAndReplace();
                }


                //SHOW TOTALS
                $replacements = $textSearchMachine->showFormattedSearchTotals();
                if (! $replacements) {
                    //flush output anyway!
                    $this->mu()->colourPrint("No replacements for  ".implode(',', $this->extensionArray));
                }
                $this->mu()->colourPrint($textSearchMachine->getOutput());
            }
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
