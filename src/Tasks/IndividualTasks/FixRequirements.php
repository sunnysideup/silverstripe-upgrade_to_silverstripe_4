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

    protected $debug = false;

    private $ignoreFolderArray = [
        '.git',
    ];

    public function getTitle()
    {
        return 'Finds requirements (Requirements::) and fixes them to be exposed properly';
    }

    public function getDescription()
    {
        return '
            Finds Requirements:: instances and fixes them to be used properly for modules
            - e.g. [vendorname] / [modulename] : location/for/my/script.js';
    }

    public function setIgnoreFolderArray($a)
    {
        $this->ignoreFolderArray = $a;

        return $this;
    }

    public function runActualTask($params = []): ?string
    {
        //replacement data patterns that will be searched for
        $replacementArray = [
            'src' => [
                'php' => [
                    'Requirements::javascript(' => [
                        'R' => '',
                    ],
                    'Requirements::css(' => [
                        'R' => '',
                    ],
                    'Requirements::themedCSS(' => [
                        'R' => '',
                    ],
                ],
            ],
        ];

        if ($this->debug) {
            $this->mu()->colourPrint($replacementArray);
        }
        foreach ($this->mu()->getExistingModuleDirLocations() as $moduleDir) {
            //Start search machine from the module location. replace API
            $textSearchMachine = new SearchAndReplaceAPI($moduleDir);
            $textSearchMachine->setIsReplacingEnabled(true);
            $textSearchMachine->addToIgnoreFolderArray($this->ignoreFolderArray);

            /*For all the different patterns listed in the replacement array
            * iterate over them such that the $path would be 'src' and $patharray would be 'php'
            * together making it ['src']['php']
            */
            foreach ($replacementArray as $path => $pathArray) {
                $path = $moduleDir . '/' . $path ?: '';
                $path = $this->mu()->checkIfPathExistsAndCleanItUp($path);
                if (! file_exists($path)) {
                    $this->mu()->colourPrint("SKIPPING ${path} as it does not exist.");
                } else {
                    $textSearchMachine->setSearchPath($path);
                    foreach ($pathArray as $extension => $extensionArray) {
                        //setting extensions to search files within
                        $textSearchMachine->setExtensions(explode('|', $extension));
                        $this->mu()->colourPrint(
                            "++++++++++++++++++++++++++++++++++++\n" .
                            "CHECKING\n" .
                            "IN ${path}\n" .
                            "FOR ${extension} FILES\n" .
                            'BASE ' . $moduleDir . "\n" .
                            "++++++++++++++++++++++++++++++++++++\n"
                        );
                        foreach ($extensionArray as $find => $findDetails) {
                            $replace = $findDetails['R'] ?? $find;
                            $caseSensitive = false;

                            $isStraightReplace = true;
                            $replacementType = 'BASIC';

                            // REPLACMENT PATTERN!
                            //Requirements::javascript(moduledirfolder/bla);
                            //Requirements::javascript(vpl: bla);
                            // $findWithPackageName = $find . strtolower($this->mu()->getPackageName());
                            $vendorAndPackageFolderNameForInstall =
                                $this->mu()->getVendorAndPackageFolderNameForInstall();
                            if (trim($find) === '') {
                                user_error("no find is specified, replace is: ${replace}");
                            }

                            foreach (['\'', '"'] as $quoteMark) {
                                $finalReplace = $find . $quoteMark . $vendorAndPackageFolderNameForInstall . ': ';
                                if (! $finalReplace && $finalReplace !== ' ') {
                                    user_error("
                                        no replace is specified, find is: ${find}.
                                        We suggest setting your final replace to a single space
                                        if you would like to replace with NOTHING.
                                    ");
                                }
                                $finalFind = $find . $quoteMark;
                                $this->mu()->colourPrint(
                                    '    --- FIND: ' . $finalFind . "\n" .
                                    '    --- REPLACE: ' . $finalReplace . "\n"
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
                            $finalFind = $vendorAndPackageFolderNameForInstall . ': silverstripe/' . $ssModule . ': ';
                            $finalReplace = 'silverstripe/' . $ssModule . ': ';
                            $this->mu()->colourPrint(
                                '    --- FIND: ' . $finalFind . "\n" .
                                '    --- REPLACE: ' . $finalReplace . "\n"
                            );
                            $textSearchMachine->setSearchKey(
                                $finalFind,
                                $isStraightReplace,
                                'silverstripe/' . $ssModule . '/@@@@double-up@@@@'
                            );
                            $textSearchMachine->setReplacementKey($finalReplace);
                            $textSearchMachine->startSearchAndReplace();
                        }

                        //SHOW TOTALS
                        $replacements = $textSearchMachine->showFormattedSearchTotals();
                        if (! $replacements) {
                            //flush output anyway!
                            $this->mu()->colourPrint("No replacements for  ${extension}");
                        }
                        $this->mu()->colourPrint($textSearchMachine->getOutput());
                    }
                }
            }
        }
        return null;
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
