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


    private $paths = [
        'Model',
        'model',
        'Pages',
        'pages',
        'PageTypes',
        'pagetypes'
    ];

    public function setPaths($a)
    {
        $this->paths = $a;

        return $this;
    }

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
        'private static $has_one'
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
            //Start search machine from the module location. replace API
            $textSearchMachine = new SearchAndReplaceAPI($moduleDir);
            $textSearchMachine->setIsReplacingEnabled(true);
            $textSearchMachine->addToIgnoreFolderArray($this->ignoreFolderArray);

            /*For all the different patterns listed in the replacement array
            * iterate over them such that the $path would be 'src' and $patharray would be 'php'
            * together making it ['src']['php']
            */
           foreach($this->paths as $path) {
                $path = $moduleDir  . '/'.$path ? : '' ;
                $path = $this->mu()->checkIfPathExistsAndCleanItUp($path);
                if (! file_exists($path)) {
                    $this->mu()->colourPrint("SKIPPING $path as it does not exist.");
                } else {
                    $textSearchMachine->setSearchPath($path);
                    foreach ($pathArray as $extension => $extensionArray) {
                        $textSearchMachine->setExtensions($this->extensionArray); //setting extensions to search files within
                        $this->mu()->colourPrint(
                            "++++++++++++++++++++++++++++++++++++\n".
                            "CHECKING\n".
                            "IN $path\n".
                            "FOR $extension FILES\n".
                            "BASE ".$moduleDir."\n".
                            "++++++++++++++++++++++++++++++++++++\n"
                        );
                        foreach ($this->findArray as $finalFind) {

                            $ignoreCase = false;
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
                            $this->mu()->colourPrint("No replacements for  $extension");
                        }
                        $this->mu()->colourPrint($textSearchMachine->getOutput());
                    }
                }
            }
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
