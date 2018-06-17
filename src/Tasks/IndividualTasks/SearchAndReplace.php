<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\SearchAndReplaceAPI;
use Sunnysideup\UpgradeToSilverstripe4\Api\LoadReplacementData;


use Sunnysideup\UpgradeToSilverstripe4\Tasks\MetaUpgraderTask;


class SearchAndReplace extends MetaUpgraderTask
{
    public function upgrader($params = [])
    {
        //do stuff ....
    }


    private $checkReplacementIssues = false;

    public function setCheckReplacementIssues($b)
    {
        $this->checkReplacementIssues = $b;

        return $this;
    }

    private $upgradeFileLocation = '.upgrade.replacement.yml';

    public function setUpgradeFileLocation($s)
    {
        $this->upgradeFileLocation = $b;

        return $this;
    }


    private $marker = "### @@@@ START UPGRADE REQUIRED @@@@ ###";

    private $endMarker = "### @@@@ END UPGRADE REQUIRED @@@@ ###";


    private $numberOfStraightReplacements = 0;

    public function getNumberOfStraightReplacements()
    {
        return intval($this->numberOfStraightReplacements);
    }

    private $numberOfAllReplacements = 0;

    public function getNumberOfAllReplacements()
    {
        return intval($this->numberOfAllReplacements);
    }


    /**
     *
     * @param String $pathLocation - enter dot for anything in current directory.
     * @param Array $ignoreFolderArray - a list of folders that should not be searched (and replaced) - folders that are automatically ignore are: CMS, SAPPHIRE, FRAMEWORK (all in lowercase)
     * outputs to screen and/or to file
     */
    public function upgradePerPath(
        $pathLocation,
        array $ignoreFolderArray = []
    ) {
        if (!file_exists($pathLocation)) {
            user_error("ERROR: could not find specified path: ".$pathLocation);
        }
        if ($this->checkReplacementIssues) {
            $this->checkReplacementIssues();
        }
        //basic checks

        //get replacements
        $replacementDataObject = new LoadReplacementData($this->mo, $this->params);

        $previousTos = $replacementDataObject->getTos();
        $migrationChecksDone = false;
        $this->numberOfStraightReplacements = 0;
        $this->numberOfAllReplacements = 0;
        $totalForOneVersion = 0;
        $numberToAdd = $this->numberOfReplacements($pathLocation, $previousTo, $ignoreFolderArray, true);
        $totalForOneVersion += $numberToAdd;
        $this->numberOfStraightReplacements += $numberToAdd;
        if ($this->numberOfStraightReplacements == 0) {
            $this->mo->colourPrint("[BASIC: DONE] migration to $previousTo for basic replacements completed.");
        } else {
            $this->mo->colourPrint("[BASIC: TO DO] migration to $previousTo for basic replacements NOT completed yet ($numberToAdd items to do).");
            $previousMigrationsDone = false;
        }
        $numberToAdd = $this->numberOfReplacements($pathLocation, $previousTo, $ignoreFolderArray, false);
        $totalForOneVersion += $numberToAdd;
        $this->numberOfAllReplacements += $numberToAdd;
        if ($this->numberOfAllReplacements == 0) {
            $this->mo->colourPrint("[COMPLEX: DONE] migration to $previousTo for complicated items completed.");
        } else {
            $this->mo->colourPrint("[COMPLEX: UNSURE] migration to $previousTo for complicated items NOT completed yet ($numberToAdd items to do).");
        }
        $this->mo->colourPrint("
------------------------------------
$totalForOneVersion items to do for $previousTo
------------------------------------"
        );
        $totalForOneVersion = 0;
        $textSearchMachine = new SearchAndReplaceAPI();

        //set basics
        $textSearchMachine->addIgnoreFolderArray($ignoreFolderArray); //setting extensions to search files within
        $textSearchMachine->setBasePath($pathLocation);
        $array = $replacementDataObject->getReplacementArrays($to);
        foreach ($array as $extension => $extensionArray) {
            $this->mo->colourPrint("
++++++++++++++++++++++++++++++++++++
CHECKING $extension FILES
++++++++++++++++++++++++++++++++++++"
            );
            $textSearchMachine->setExtensions(array($extension)); //setting extensions to search files within
            foreach ($extensionArray as $replaceArray) {
                $find = $replaceArray[0];
                //$replace = $replaceArray[1]; unset($replaceArray[1]);
                //$fullReplacement = (isset($replaceArray[2]) ? "/* ".$replaceArray[2]." */\n" : "").$replaceArray[1];
                $fullReplacement = "";
                $isStraightReplace = true;
                if (isset($replaceArray[2])) {
                    // Has comment
                    $isStraightReplace = false;
                    $fullReplacement = "/*\n".$this->marker."\nFIND: ".$replaceArray[0]."\nNOTE: ".$replaceArray[2]." \n".$this->endMarker."\n*/".$replaceArray[1];
                } else { // Straight replace
                    $fullReplacement = $replaceArray[1];
                }
                $comment = isset($replaceArray[2]) ? $replaceArray[2] : "";
                $codeReplacement = $replaceArray[1];
                if (!$find) {
                    user_error("no find is specified, replace is: $replace");
                }
                if (!$fullReplacement) {
                    user_error("no replace is specified, find is: $find");
                }
                if (!$markStickingPoints && !$isStraightReplace) {
                    continue;
                }
                $textSearchMachine->setSearchKey($find, 0, $isStraightReplace ? "BASIC" : "COMPLEX");
                $textSearchMachine->setReplacementKey($fullReplacement);
                $textSearchMachine->startSearching();//starting search
                //output - only write to log for real replacements!
                //$textSearchMachine->writeLogToFile($logFileLocation);
                //$textSearchMachine->showLog();//showing log
            }
            $replacements = $textSearchMachine->showFormattedSearchTotals(false);
            if ($replacements) {
                $this->mo->colourPrint($textSearchMachine->getOutput());
            } else {
                //flush output anyway!
                $textSearchMachine->getOutput();
                $this->mo->colourPrint("No replacements for  $extension");
            }
        }
        return $this->printItNow();
    }

    /**
     *
     * @var Int
     */
    private function numberOfReplacements(
        $pathLocation = ".",
        $ignoreFolderArray = array()
    ) {
        //basic checks
        $total = 0;
        $textSearchMachine = new SearchAndReplaceAPI();

        //get replacements
        $replacementData = new LoadReplacementData();
        $array = $replacementData->getReplacementArrays();

        //set basics
        $textSearchMachine->addIgnoreFolderArray($ignoreFolderArray); //setting extensions to search files within
        $textSearchMachine->setBasePath($pathLocation);
        foreach ($array as $extension => $extensionArray) {
            $textSearchMachine->setExtensions(array($extension)); //setting extensions to search files within
            foreach ($extensionArray as $replaceArray) {
                $find = $replaceArray[0];
                $isStraightReplace = isset($replaceArray[2]) ? true : false;
                if ($isStraightReplace && $simpleOnly) {
                    // Has comment
                    continue;
                } elseif (!$isStraightReplace && !$simpleOnly) {
                    continue;
                }
                $textSearchMachine->setSearchKey($find, 0, $isStraightReplace ? "BASIC" : "COMPLEX");
                $textSearchMachine->setFutureReplacementKey("TEST ONLY");
                $textSearchMachine->startSearching();//starting search
            }
            //IMPORTANT!
            $total += $textSearchMachine->showFormattedSearchTotals(true);
        }
        //flush output anyway!
        $textSearchMachine->getOutput();

        return $total;
    }

    /**
     * 1. check that one find is not used twice:
     * find can be found 2x
     *
     */
    private function checkReplacementIssues()
    {
        $r = new ReplacementData();
        $arr = $r->getReplacementArrays(null);
        $arrTos = array();
        $arrLanguages = $r->getLanguages();
        $fullFindArray = $r->getFlatFindArray();
        $fullReplaceArray = $r->getFlatReplacedArray();

        //1, check that one find may not stop another replacement.
        foreach ($arrLanguages as $language) {
            if (!isset($fullFindArray[$language])) {
                continue;
            }
            unset($keyOuterDoneSoFar);
            $keyOuterDoneSoFar = array();
            foreach ($fullFindArray[$language] as $keyOuter => $findStringOuter) {
                $keyOuterDoneSoFar[$keyOuter] = true;
                foreach ($fullFindArray[$language] as $keyInner => $findStringInner) {
                    if (!isset($keyOuterDoneSoFar[$keyInner])) {
                        if ($keyOuter != $keyInner) {
                            $findStringOuterReplaced = str_replace($findStringInner, "...", $findStringOuter);
                            if ($findStringOuter == $findStringInner || $findStringOuterReplaced != $findStringOuter) {
                                $this->mo->colourPrint("
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
            $keyOuterDoneSoFar = array();
            foreach ($fullReplaceArray[$language] as $keyOuter => $findStringOuter) {
                $keyOuterDoneSoFar[$keyOuter] = true;
                foreach ($fullFindArray[$language] as $keyInner => $findStringInner) {
                    if (isset($keyOuterDoneSoFar[$keyInner])) {
                        if ($keyOuter != $keyInner) {
                            $findStringOuterReplaced = str_replace($findStringInner, "...", $findStringOuter);
                            if ($findStringOuter == $findStringInner || $findStringOuterReplaced != $findStringOuter) {
                                $this->mo->colourPrint("
ERROR in $language: \t\t there is a replacement (A) that was earlier tried to be found (B).
---- A: ($keyOuter): \t\t $findStringOuter
---- B: ($keyInner): \t\t $findStringInner");
                            }
                        }
                    }
                }
            }
        }
        $this->mo->colourPrint("");
    }


}
