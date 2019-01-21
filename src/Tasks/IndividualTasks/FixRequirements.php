<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\SearchAndReplaceAPI;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Replaces a bunch of code snippets in preparation of the upgrade.
 * Controversial replacements will be replaced with a comment
 * next to it so you can review replacements easily.
 */
class FixRequirements extends Task
{
    protected $taskStep = 's30';

    public function getTitle()
    {
        return 'Finds requirements (Requirements::) and fixes them to be exposed properly';
    }

    public function getDescription()
    {
        return '
            Finds Requirements:: instances and fixes them to be used properly for modules - e.g. [vendorname] / [modulename] : location/for/my/script.js' ;
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


    public function runActualTask($params = [])
    {
        //replacement data patterns that will be searched for
        $replacementArray = [
            'src' => [
                'php' => [
                    'Requirements::javascript(' => [
                        'R' => ''
                    ],
                    'Requirements::css(' => [
                        'R' => ''
                    ],
                    'Requirements::themedCSS(' => [
                        'R' => ''
                    ]
                ]
            ]
        ];

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
            foreach ($replacementArray as $path => $pathArray) {
                $path = $moduleDir  . '/'.$path ? : '' ;
                $path = $this->mu()->checkIfPathExistsAndCleanItUp($path);
                if (! file_exists($path)) {
                    $this->mu()->colourPrint("SKIPPING $path as it does not exist.");
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
                            $ignoreCase = true;
                            $caseSensitive = ! $ignoreCase;

                            $isStraightReplace = true;

                            // REPLACMENT PATTERN!
                            //Requirements::javascript(moduledirfolder/bla);
                            //Requirements::javascript(vpl: bla);
                            $findWithPackageName = $find.strtolower($this->mu()->getPackageName());
                            $vendorAndPackageFolderNameForInstall = $this->mu()->getVendorAndPackageFolderNameForInstall();
                            if (!$find) {
                                user_error("no find is specified, replace is: $replace");
                            }
                            $replacementType = $isStraightReplace ? "BASIC" : "COMPLEX";

                            foreach (['\'', '"'] as $quoteMark) {
                                $finalReplace = $find.$quoteMark.$vendorAndPackageFolderNameForInstall.': ';
                                if (!$finalReplace) {
                                    user_error("no replace is specified, find is: $find");
                                }
                                $finalFind = $find.$quoteMark;
                                $this->mu()->colourPrint(
                                    '    --- FIND: '.$finalFind."\n".
                                    '    --- REPLACE: '.$finalReplace."\n"
                                );

                                $textSearchMachine->setSearchKey($finalFind, $caseSensitive, $replacementType);
                                $textSearchMachine->setReplacementKey($finalReplace);
                                $textSearchMachine->startSearchAndReplace();
                            }
                        }

                        //fix double-ups
                        //fixes things like
                        //vendor/packagename: silverstripe/admin
                        //to
                        //silverstripe/admin: only
                        foreach (['cms', 'framework', 'siteconfig', 'reports'] as $ssModule) {
                            $isStraightReplace = true;
                            $finalFind = $vendorAndPackageFolderNameForInstall.': silverstripe/'.$ssModule.': ';
                            $finalReplace = 'silverstripe/'.$ssModule.': ';
                            $this->mu()->colourPrint(
                                '    --- FIND: '.$finalFind."\n".
                                '    --- REPLACE: '.$finalReplace."\n"
                            );
                            $textSearchMachine->setSearchKey($finalFind, $isStraightReplace, 'silverstripe/'.$ssModule.'/@@@@double-up@@@@');
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
