<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\SearchAndReplaceAPI;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Replaces a bunch of code snippets in preparation of the upgrade.
 * Controversial replacements will be replaced with a comment
 * next to it so you can review replacements easily.
 */
class FixBadUseStatements extends Task
{
    protected $taskStep = 's50';

    public function getTitle()
    {
        return 'Look for single use statements and comment them out as they are not correct.';
    }

    public function getDescription()
    {
        return '
            Goes through code and removes, for example, "use bool;", lines, as they do not make sense.' ;
    }

    protected $debug = false;

    private $checkReplacementIssues = false;

    public function setCheckReplacementIssues($b)
    {
        $this->checkReplacementIssues = $b;

        return $this;
    }

    private $ignoreFolderArray = [
        ".git"
    ];

    public function setIgnoreFolderArray($a)
    {
        $this->ignoreFolderArray = $a;

        return $this;
    }

    protected $replacementArray = [
        'src' => [
            'php' => [
                'use bool;',
                'use string;',
                'use int;',
                'use array;'
            ]
        ]
    ];

    public function setReplacementArray($a)
    {
        $this->replacementArray = $a;

        return $this;
    }


    public function runActualTask($params = [])
    {
        //replacement data patterns that will be searched for

        if ($this->debug) {
            $this->mu()->colourPrint(print_r($this->replacementArray, 1));
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
            foreach ($this->replacementArray as $path => $pathArray) {
                $path = $moduleDir  . '/'.$path ? : '' ;
                $path = $this->mu()->checkIfPathExistsAndCleanItUp($path);
                if (! file_exists($path)) {
                    $this->mu()->colourPrint("SKIPPING $path as it does not exist.");
                } else {
                    $textSearchMachine->setSearchPath($path);
                    foreach ($pathArray as $extension => $extensionArray) {
                        $textSearchMachine->setExtensions(explode('|', $extension)); //setting extensions to search files within
                        foreach ($extensionArray as $find) {
                            $ignoreCase = true;
                            $caseSensitive = false;
                            $isStraightReplace = true;
                            $replacementType = "BASIC";

                            // $this->mu()->colourPrint(
                            //     "++++++++++++++++++++++++++++++++++++\n".
                            //     "CHECKING\n".
                            //     "IN $path\n".
                            //     "FOR $extension FILES\n".
                            //     "BASE ".$moduleDir."\n".
                            //     "FIND '".$find."'\n".
                            //     "REPLACE ''\n".
                            //     "++++++++++++++++++++++++++++++++++++\n"
                            // );
                            $textSearchMachine->setSearchKey($find, $caseSensitive, $replacementType);
                            $textSearchMachine->setReplacementKey('');
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