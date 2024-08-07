<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\LoadReplacementData;
use Sunnysideup\UpgradeToSilverstripe4\Api\SearchAndReplaceAPI;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Replaces a bunch of code snippets in preparation of the upgrade.
 * Controversial replacements will be replaced with a comment
 * next to it so you can review replacements easily.
 */
class SearchAndReplace extends Task
{
    protected $taskStep = 's30';

    /**
     * for debugging purposes
     * @var bool
     */
    protected $debug = false;

    /**
     * check if there are double-ups in replacement.
     * @var bool
     */
    protected $checkReplacementIssues = false;

    /**
     * string used to show issues in replacement in the actual code being replaced.
     *
     * @var string
     */
    protected $replacementHeader = 'automated upgrade';

    /**
     * folder containing the replacement file
     *
     * @var string
     */
    protected $folderContainingReplacementData = '';

    /**
     * list of folders to ignore in search and replace
     * @var array
     */
    protected $ignoreFolderArray = [
        '.git',
        '.svn',
    ];

    protected $runInRootDir = false;

    /**
     * the names of the folder that contains the data we need
     * e.g. SS4 / SS37
     * IMPORTANT!
     *
     * @var array
     */
    protected $sourceFolders = [
        'SS4',
    ];

    protected $commitAndPush = true;

    public function getTitle()
    {
        return 'Search and Replace';
    }

    public function getDescription()
    {
        return '
            Replaces a bunch of code snippets in preparation of the upgrade.
            Controversial replacements will be replaced with a comment
            next to it so you can review replacements easily.';
    }

    public function setCheckReplacementIssues(bool $b)
    {
        $this->checkReplacementIssues = $b;

        return $this;
    }

    public function setIgnoreFolderArray(array $a)
    {
        $this->ignoreFolderArray = $a;

        return $this;
    }

    public function setRunInRootDir(bool $b)
    {
        $this->runInRootDir = $b;

        return $this;
    }

    public function setCommitAndPush(bool $b)
    {
        $this->commitAndPush = $b;

        return $this;
    }

    public function setFolderContainingReplacementData(string $s)
    {
        $this->folderContainingReplacementData = $s;

        return $this;
    }


    public function setSourceFolders(array $a)
    {
        $this->sourceFolders = $a;

        return $this;
    }

    public function runActualTask($params = []): ?string
    {
        //replacement data
        $replacementDataObjects = $this->getReplacementDataObjects();
        foreach ($replacementDataObjects as $replacementDataObject) {
            if ($this->checkReplacementIssues) {
                $this->checkReplacementDataIssues($replacementDataObject);
            }

            $replacementArray = $replacementDataObject->getReplacementArrays();

            if ($this->debug) {
                $this->mu()->colourPrint($replacementArray);
            }

            //replace API
            if($this->runInRootDir) {
                $list = [$this->mu()->getWebRootDirLocation()];
            } else {
                $list = $this->mu()->getExistingModuleDirLocationsWithThemeFolders();
            }
            foreach ($list as $moduleOrThemeDir) {
                $textSearchMachine = new SearchAndReplaceAPI($moduleOrThemeDir);
                $textSearchMachine->setIsReplacingEnabled(true);
                $textSearchMachine->addToIgnoreFolderArray($this->ignoreFolderArray);

                foreach ($replacementArray as $path => $pathArray) {
                    $path = $moduleOrThemeDir . '/' . $path ?: '';
                    $path = $this->mu()->checkIfPathExistsAndCleanItUp($path);
                    if (! file_exists($path)) {
                        $this->mu()->colourPrint("SKIPPING ".$path);
                    } else {
                        $textSearchMachine->setSearchPath($path);
                        foreach ($pathArray as $extension => $extensionArray) {
                            //setting extensions to search files within
                            $textSearchMachine->setExtensions(explode('|', $extension));
                            $this->mu()->colourPrint(
                                "++++++++++++++++++++++++++++++++++++\n" .
                                "CHECKING\n" .
                                "IN ".$path."\n" .
                                "FOR ".$extension." FILES\n" .
                                'BASE ' . $moduleOrThemeDir . "\n" .
                                "++++++++++++++++++++++++++++++++++++\n"
                            );
                            foreach ($extensionArray as $find => $findDetails) {
                                $replace = isset($findDetails['R']) ? $findDetails['R'] : $find;
                                $comment = isset($findDetails['C']) ? $findDetails['C'] : '';
                                $ignoreCase = isset($findDetails['I']) ? $findDetails['I'] : false;
                                $caseSensitive = ! $ignoreCase;
                                $isRegex = false;
                                //$replace = $replaceArray[1]; unset($replaceArray[1]);
                                //$fullReplacement =
                                //   (isset($replaceArray[2]) ? "/* ".$replaceArray[2]." */\n" : "").$replaceArray[1];
                                $fullReplacement = $replace;
                                $isStraightReplace = true;
                                if ($comment) {
                                    $isStraightReplace = false;
                                }
                                if (! $find) {
                                    user_error("no find is specified, replace is: ${replace}");
                                }
                                if (! $fullReplacement) {
                                    user_error('
                                        No replace is specified, find is: ${find}.
                                        We suggest setting your final replace to a single space if
                                        you would like to replace with NOTHING.
                                    ');
                                }
                                $replaceKey = $isStraightReplace ? 'BASIC' : 'COMPLEX';

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
                                $this->mu()->colourPrint("No replacements for  ${extension}");
                            }
                            $this->mu()->colourPrint($textSearchMachine->getOutput());
                        }
                    }
                }
            }
        }
        return null;
    }

    protected function hasCommitAndPush()
    {
        return $this->commitAndPush;
    }

    protected function getReplacementDataObjects(): array
    {
        $array = [];
        foreach ($this->sourceFolders as $sourceFolder) {
            $array[] = new LoadReplacementData(
                $this->mu(),
                $this->folderContainingReplacementData,
                $sourceFolder
            );
        }

        return $array;
    }

    /**
     * 1. check that one find is not used twice:
     * find can be found 2x
     * @param mixed $replacementDataObject
     */
    private function checkReplacementDataIssues($replacementDataObject)
    {
        $replacementDataObject->getReplacementArrays(null);
        $arrLanguages = $replacementDataObject->getLanguages();
        $fullFindArray = $replacementDataObject->getFlatFindArray();
        $fullReplaceArray = $replacementDataObject->getFlatReplacedArray();

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
                    if (! isset($keyOuterDoneSoFar[$keyInner])) {
                        if ($keyOuter !== $keyInner) {
                            $findStringOuterReplaced = str_replace($findStringInner, '...', $findStringOuter);
                            if ($findStringOuter === $findStringInner || $findStringOuterReplaced !== $findStringOuter) {
                                $this->mu()->colourPrint("
ERROR in ".$language.": \t\t we are trying to find the same thing twice (A and B)
---- A: (".$keyOuter."): \t\t ".$findStringOuter."
---- B: (".$keyInner."): \t\t ".$findStringInner);
                            }
                        }
                    }
                }
            }
        }

        //2. check that a replacement is not mentioned before the it is being replaced
        foreach ($arrLanguages as $language) {
            if (! isset($fullReplaceArray[$language])) {
                continue;
            }
            unset($keyOuterDoneSoFar);
            $keyOuterDoneSoFar = [];
            foreach ($fullReplaceArray[$language] as $keyOuter => $findStringOuter) {
                $keyOuterDoneSoFar[$keyOuter] = true;
                foreach ($fullFindArray[$language] as $keyInner => $findStringInner) {
                    if (isset($keyOuterDoneSoFar[$keyInner])) {
                        if ($keyOuter !== $keyInner) {
                            $findStringOuterReplaced = str_replace($findStringInner, '...', $findStringOuter);
                            if ($findStringOuter === $findStringInner || $findStringOuterReplaced !== $findStringOuter) {
                                $this->mu()->colourPrint("
ERROR in ".$language.": \t\t there is a replacement (A) that was earlier tried to be found (B).
---- A: (".$keyOuter."): \t\t ".$findStringOuter."
---- B: (".$keyInner."): \t\t ".$findStringInner);
                            }
                        }
                    }
                }
            }
        }
        $this->mu()->colourPrint('');
    }
}
