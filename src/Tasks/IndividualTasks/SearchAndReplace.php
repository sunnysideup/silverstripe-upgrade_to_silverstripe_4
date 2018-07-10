<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\SearchAndReplaceAPI;
use Sunnysideup\UpgradeToSilverstripe4\Api\LoadReplacementData;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class SearchAndReplace extends Task
{

    public function getTitle()
    {
        return 'Search and Replace';
    }

    public function getDescription()
    {
        return '
            Replaces a bunch of code snippets in preparation of the upgrade.
            Controversial replacements will be replaced with a comment
            next to it so you can review replacements easily.' ;
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

    private $startMarker = "### @@@@ START UPGRADE REQUIRED @@@@ ###";

    private $endMarker = "### @@@@ END UPGRADE REQUIRED @@@@ ###";

    public function runActualTask($params = [])
    {
        if ($this->checkReplacementIssues) {
            $this->checkReplacementDataIssues();
        }

        //replacement data
        $replacementDataObject = new LoadReplacementData($this->mu, $this->params);
        $replacementArray = $replacementDataObject->getReplacementArrays();

        if ($this->debug) {
            $this->mu->colourPrint(print_r($replacementArray, 1));
        }

        //replace API
        $textSearchMachine = new SearchAndReplaceAPI($this->mu->getModuleDirLocation());
        $textSearchMachine->setIsReplacingEnabled(true);
        $textSearchMachine->addToIgnoreFolderArray($this->ignoreFolderArray);

        foreach ($replacementArray as $path => $pathArray) {
            $path = $this->mu->getModuleDirLocation()  . '/'.$path ? : '' ;
            $path = $this->mu->checkIfPathExistsAndCleanItUp($path);
            if (!file_exists($path)) {
                $this->mu->colourPrint("SKIPPING $path");
            } else {
                $textSearchMachine->setSearchPath($path);
                foreach ($pathArray as $extension => $extensionArray) {
                    $textSearchMachine->setExtensions(explode('|', $extension)); //setting extensions to search files within
                    $this->mu->colourPrint(
                        "++++++++++++++++++++++++++++++++++++\n".
                        "CHECKING\n".
                        "IN $path\n".
                        "FOR $extension FILES\n".
                        "BASE ".$this->mu->getModuleDirLocation()."\n".
                        "++++++++++++++++++++++++++++++++++++\n"
                    );
                    foreach ($extensionArray as $find => $findDetails) {
                        $replace = isset($findDetails['R'])       ? $findDetails['R'] : $find;
                        $comment = isset($findDetails['C'])       ? $findDetails['C'] : '';
                        $ignoreCase = isset($findDetails['I'])    ? $findDetails['I'] : false;
                        $caseSensitive = ! $ignoreCase;
                        //$replace = $replaceArray[1]; unset($replaceArray[1]);
                        //$fullReplacement = (isset($replaceArray[2]) ? "/* ".$replaceArray[2]." */\n" : "").$replaceArray[1];
                        $fullReplacement = '';
                        $isStraightReplace = $comment ? false : true;
                        if ($isStraightReplace) {
                            $fullReplacement = $replace;
                        } else {
                            $fullReplacement = $replace."/*\n".$this->startMarker."\nFIND: ".$find."\nNOTE: ".$comment." \n".$this->endMarker."\n*/";
                        }
                        if (!$find) {
                            user_error("no find is specified, replace is: $replace");
                        }
                        if (!$fullReplacement) {
                            user_error("no replace is specified, find is: $find");
                        }
                        $replaceKey = $isStraightReplace ? "BASIC" : "COMPLEX";

                        $textSearchMachine->setSearchKey($find, $caseSensitive, $replaceKey);
                        $textSearchMachine->setReplacementKey($fullReplacement);
                        $textSearchMachine->startSearchAndReplace();
                    }
                    $replacements = $textSearchMachine->showFormattedSearchTotals();
                    if ($replacements) {
                    } else {
                        //flush output anyway!
                        $this->mu->colourPrint("No replacements for  $extension");
                    }
                    $this->mu->colourPrint($textSearchMachine->getOutput());
                }
            }
        }
    }

    /**
     * 1. check that one find is not used twice:
     * find can be found 2x
     *
     */
    private function checkReplacementDataIssues()
    {
        $r = new ReplacementData();
        $arr = $r->getReplacementArrays(null);
        $arrTos = [];
        $arrLanguages = $r->getLanguages();
        $fullFindArray = $r->getFlatFindArray();
        $fullReplaceArray = $r->getFlatReplacedArray();

        //1. check that one find may not stop another replacement.
        foreach ($arrLanguages as $language) {
            if (! isset($fullFindArray[$language])) {
                continue;
            }
            unset($keyOuterDoneSoFar);
            $keyOuterDoneSoFar = [];
            foreach ($fullFindArray[$language] as $keyOuter => $findStringOuter) {
                $keyOuterDoneSoFar[$keyOuter] = true;
                foreach ($fullFindArray[$language] as $keyInner => $findStringInner) {
                    if (!isset($keyOuterDoneSoFar[$keyInner])) {
                        if ($keyOuter != $keyInner) {
                            $findStringOuterReplaced = str_replace($findStringInner, "...", $findStringOuter);
                            if ($findStringOuter == $findStringInner || $findStringOuterReplaced != $findStringOuter) {
                                $this->mu->colourPrint("
ERROR in $language: \t\t we are trying to find the same thing twice (A and B)
---- A: ($keyOuter): \t\t $findStringOuter
---- B: ($keyInner): \t\t $findStringInner");
                            }
                        }
                    }
                }
            }
        }

        //2. check that a replacement is not mentioned before the it is being replaced
        foreach ($arrLanguages as $language) {
            if (!isset($fullReplaceArray[$language])) {
                continue;
            }
            unset($keyOuterDoneSoFar);
            $keyOuterDoneSoFar = [];
            foreach ($fullReplaceArray[$language] as $keyOuter => $findStringOuter) {
                $keyOuterDoneSoFar[$keyOuter] = true;
                foreach ($fullFindArray[$language] as $keyInner => $findStringInner) {
                    if (isset($keyOuterDoneSoFar[$keyInner])) {
                        if ($keyOuter != $keyInner) {
                            $findStringOuterReplaced = str_replace($findStringInner, "...", $findStringOuter);
                            if ($findStringOuter == $findStringInner || $findStringOuterReplaced != $findStringOuter) {
                                $this->mu->colourPrint("
ERROR in $language: \t\t there is a replacement (A) that was earlier tried to be found (B).
---- A: ($keyOuter): \t\t $findStringOuter
---- B: ($keyInner): \t\t $findStringInner");
                            }
                        }
                    }
                }
            }
        }
        $this->mu->colourPrint("");
    }
}
