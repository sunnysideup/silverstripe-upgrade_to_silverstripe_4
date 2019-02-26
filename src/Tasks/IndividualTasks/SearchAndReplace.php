<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\SearchAndReplaceAPI;
use Sunnysideup\UpgradeToSilverstripe4\ReplacementData\LoadReplacementData;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Replaces a bunch of code snippets in preparation of the upgrade.
 * Controversial replacements will be replaced with a comment
 * next to it so you can review replacements easily.
 */
class SearchAndReplace extends Task
{
    protected $taskStep = 's30';

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

    protected $checkReplacementIssues = false;

    protected $replacementHeader = 'upgrade to SS4';

    public function setCheckReplacementIssues($b)
    {
        $this->checkReplacementIssues = $b;

        return $this;
    }

    protected $ignoreFolderArray = [
        ".git"
    ];

    public function setIgnoreFolderArray($a)
    {
        $this->ignoreFolderArray = $a;

        return $this;
    }

    protected $alternativePathForReplacementData = '';

    public function setAlternativePathForReplacementData($s)
    {
        $this->alternativePathForReplacementData = $s;

        return $this;
    }

    public function runActualTask($params = [])
    {
        if ($this->checkReplacementIssues) {
            $this->checkReplacementDataIssues();
        }

        //replacement data
        $replacementDataObject = new LoadReplacementData(
            $this->mu(),
            $this->alternativePathForReplacementData,
            $this->params
        );
        $replacementArray = $replacementDataObject->getReplacementArrays();

        if ($this->debug) {
            $this->mu()->colourPrint(print_r($replacementArray, 1));
        }

        //replace API
        foreach($this->mu()->getExistingModuleDirLocations() as $moduleDir) {
            $textSearchMachine = new SearchAndReplaceAPI($moduleDir);
            $textSearchMachine->setIsReplacingEnabled(true);
            $textSearchMachine->addToIgnoreFolderArray($this->ignoreFolderArray);

            foreach ($replacementArray as $path => $pathArray) {
                $path = $moduleDir  . '/'.$path ? : '' ;
                $path = $this->mu()->checkIfPathExistsAndCleanItUp($path);
                if (!file_exists($path)) {
                    $this->mu()->colourPrint("SKIPPING $path");
                } else {
                    $textSearchMachine->setSearchPath($path);
                    foreach ($pathArray as $extension => $extensionArray) {
                        $textSearchMachine->setExtensions(explode('|', $extension)); //setting extensions to search files within
                        $this->mu()->colourPrint(
                            "++++++++++++++++++++++++++++++++++++\n".
                            "CHECKING\n".
                            "IN $path\n".
                            "FOR $extension FILES\n".
                            "BASE ".$moduleDir."\n".
                            "++++++++++++++++++++++++++++++++++++\n"
                        );
                        foreach ($extensionArray as $find => $findDetails) {
                            $replace = isset($findDetails['R'])       ? $findDetails['R'] : $find;
                            $comment = isset($findDetails['C'])       ? $findDetails['C'] : '';
                            $ignoreCase = isset($findDetails['I'])    ? $findDetails['I'] : false;
                            $caseSensitive = ! $ignoreCase;
                            //$replace = $replaceArray[1]; unset($replaceArray[1]);
                            //$fullReplacement = (isset($replaceArray[2]) ? "/* ".$replaceArray[2]." */\n" : "").$replaceArray[1];
                            $fullReplacement = $replace;
                            $isStraightReplace = true;
                            if ($comment) {
                                $isStraightReplace = false;
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
                            if ($comment) {
                                $textSearchMachine->setComment($comment);
                            }
                            $textSearchMachine->setReplacementHeader($this->replacementHeader);
                            $textSearchMachine->startSearchAndReplace();
                        }
                        $replacements = $textSearchMachine->showFormattedSearchTotals();
                        if ($replacements) {
                        } else {
                            //flush output anyway!
                            $this->mu()->colourPrint("No replacements for  $extension");
                        }
                        $this->mu()->colourPrint($textSearchMachine->getOutput());
                    }
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
                                $this->mu()->colourPrint("
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
                                $this->mu()->colourPrint("
ERROR in $language: \t\t there is a replacement (A) that was earlier tried to be found (B).
---- A: ($keyOuter): \t\t $findStringOuter
---- B: ($keyInner): \t\t $findStringInner");
                            }
                        }
                    }
                }
            }
        }
        $this->mu()->colourPrint("");
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
