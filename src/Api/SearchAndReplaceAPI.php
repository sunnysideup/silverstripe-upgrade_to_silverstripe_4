<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Api;

/**
 * @BasedOn  :  MA Razzaque Rupom <rupom_315@yahoo.com>, <rupom.bd@gmail.com>
 *             Moderator, phpResource Group(http://groups.yahoo.com/group/phpresource/)
 *             URL: http://rupom.wordpress.com
 */

class SearchAndReplaceAPI
{
    //generic search settings

    private $debug = false;

    private $basePath = '';

    private $isReplacingEnabled = false;

    //specific search settings

    private $searchKey = '';

    private $replacementKey = '';

    private $comment = '';

    private $startMarker = '### @@@@ START REPLACEMENT @@@@ ###';

    private $endMarker = '### @@@@ STOP REPLACEMENT @@@@ ###';

    private $replacementHeader = '';

    private $replacementType = '';

    private $caseSensitive = true;

    private $ignoreFrom = [
        '//',
        '#',
        '/**',
    ];

    private $fileReplacementMaxCount = 0;

    private $ignoreUntil = [
        '//',
        '#',
        '*/',
    ];

    private $ignoreFileIfFound = [];

    private $fileNameMustContain = [];

    // special stuff

    private $magicReplacers = [
        '[SEARCH_REPLACE_CLASS_NAME_GOES_HERE]' => 'classNameOfFile',
    ];

    // files

    private $fileFinder = null;

    //stats and reporting

    private $logString = ''; //details of one search

    private $errorText = ''; //details of one search

    private $totalFound = 0; //total matches in one search

    private $output = ''; //buffer of output, until it is retrieved

    // static counts

    private $searchKeyTotals = [];

    private $folderTotals = [];

    private $totalTotal = 0;

    /**
     * magic replacement functions
     */
    private static $class_name_cache = [];

    private static $finder = null;

    public function __construct($basePath = '')
    {
        $this->basePath = $basePath;
        $this->fileFinder = new FindFiles();
    }

    //================================================
    // Setters Before Run
    //================================================

    /**
     *   @return $this
     */
    public function setDebug($b)
    {
        $this->debug = $b;

        return $this;
    }

    /**
     *   @return $this
     */
    public function setIsReplacingEnabled($b)
    {
        $this->isReplacingEnabled = $b;

        return $this;
    }

    /**
     *   Sets folders to ignore
     *   @param array $ignoreFolderArray
     *   @return self
     */
    public function setIgnoreFolderArray($ignoreFolderArray = [])
    {
        $this->fileFinder->setIgnoreFolderArray($ignoreFolderArray);

        return $this;
    }

    /**
     *   Sets folders to ignore
     *   @param array $ignoreFolderArray
     *   @return self
     */
    public function addToIgnoreFolderArray($ignoreFolderArray = [])
    {
        $this->fileFinder->addToIgnoreFolderArray($ignoreFolderArray);

        return $this;
    }

    /**
     * remove ignore folders
     */
    public function resetIgnoreFolderArray()
    {
        $this->fileFinder->resetIgnoreFolderArray();

        return $this;
    }

    public function setBasePath($pathLocation)
    {
        $this->basePath = $pathLocation;
        $this->fileFinder->setBasePath($pathLocation);

        return $this;
    }

    public function setSearchPath($pathLocation)
    {
        $this->fileFinder->setSearchPath($pathLocation);

        return $this;
    }

    /**
     *   Sets extensions to look
     *   @param array $extensions
     */
    public function setExtensions($extensions = [])
    {
        $this->fileFinder->setExtensions($extensions);

        return $this;
    }

    /**
     *   Sets extensions to look
     *   @param bool $boolean
     */
    public function setFindAllExts($boolean = true)
    {
        $this->fileFinder->setFindAllExts($boolean);

        return $this;
    }

    public function setStartMarker($s)
    {
        $this->startMarker = $s;

        return $this;
    }

    public function setEndMarker($s)
    {
        $this->endMarker = $s;

        return $this;
    }

    public function setReplacementHeader($s)
    {
        $this->replacementHeader = $s;

        return $this;
    }

    public function setIgnoreFileIfFound($a)
    {
        if (is_string($a)) {
            $a = [$a];
        }
        $this->ignoreFileIfFound = $a;

        return $this;
    }

    public function setFileNameMustContain($a)
    {
        if (is_string($a)) {
            $a = [$a];
        }
        $this->fileNameMustContain = $a;

        return $this;
    }

    public function setFileReplacementMaxCount($i)
    {
        $this->fileReplacementMaxCount = $i;

        return $this;
    }

    //================================================
    // Setters Before Every Search
    //================================================

    /**
     * Sets search key and case sensitivity
     * @param string $searchKey,
     * @param bool $caseSensitive
     */
    public function setSearchKey($searchKey, $caseSensitive = false, $replacementType = 'noType')
    {
        $this->searchKey = $searchKey;
        $this->caseSensitive = $caseSensitive;
        $this->replacementType = $replacementType;
        //reset comment
        $this->comment = '';

        return $this;
    }

    /**
     *   Sets key to replace searchKey with
     *   @param string $replacementKey
     */
    public function setReplacementKey($replacementKey)
    {
        $this->replacementKey = $replacementKey;
        $this->setIsReplacingEnabled(true);

        return $this;
    }

    /**
     *   Sets a comment to go with the replacement.
     *   @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * makes a comment into a PHP proper comment (like this one)
     * @return string
     */
    public function getFullComment()
    {
        $string = '';
        if ($this->comment) {
            $string .=
            PHP_EOL .
                '/**' . PHP_EOL .
                '  * ' . $this->startMarker . PHP_EOL;
            if ($this->replacementHeader) {
                $string .= '  * WHY: ' . $this->replacementHeader . PHP_EOL;
            }
            $caseSensitiveStatement = ($this->caseSensitive ? 'case sensitive' : 'ignore case');
            $replacementTypeStatement = ($this->replacementType ? ' (' . $this->replacementType . ')' : '');
            $string .=
                '  * OLD: ' . $this->searchKey . ' (' . $caseSensitiveStatement . ')' . PHP_EOL .
                '  * NEW: ' . $this->replacementKey . $replacementTypeStatement . PHP_EOL .
                '  * EXP: ' . $this->comment . PHP_EOL .
                '  * ' . $this->endMarker . PHP_EOL .
                '  */' .
                PHP_EOL;
        }

        return $string;
    }

    //================================================
    // Get FINAL output
    //================================================

    /**
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * returns full output
     * and clears it.
     * @return string
     */
    public function getOutput()
    {
        $output = $this->output;
        $this->output = '';

        return $output;
    }

    /**
     * returns full log
     * and clears it.
     * @return string
     */
    public function getLog()
    {
        $logString = $this->logString;
        $this->logString = '';

        return $logString;
    }

    /**
     * returns the TOTAL TOTAL number of
     * found replacements
     */
    public function getTotalTotalSearches()
    {
        return $this->totalTotal;
    }

    /**
     * should be run at the end of an extension.
     */
    public function showFormattedSearchTotals($suppressOutput = false)
    {
        $totalSearches = 0;
        foreach ($this->searchKeyTotals as $searchKey => $total) {
            $totalSearches += $total;
        }
        if ($suppressOutput) {
            //do nothing
        } else {
            $flatArray = $this->fileFinder->getFlatFileArray();
            if ($flatArray && ! is_array($flatArray)) {
                $this->addToOutput("\n" . $flatArray . "\n");
            } else {
                $this->addToOutput("\n--------------\nFiles Searched\n--------------\n");
                foreach ($flatArray as $file) {
                    $strippedFile = str_replace($this->basePath, '', $file);
                    $this->addToOutput($strippedFile . "\n");
                }
            }
            $folderSimpleTotals = [];
            $realBase = (string) realpath($this->basePath);
            $this->addToOutput("\n--------------\nSummary: by search key\n--------------\n");
            arsort($this->searchKeyTotals);
            foreach ($this->searchKeyTotals as $searchKey => $total) {
                $this->addToOutput(sprintf("%d:\t %s\n", $total, $searchKey));
            }
            $this->addToOutput("\n--------------\nSummary: by directory\n--------------\n");
            arsort($this->folderTotals);
            foreach ($this->folderTotals as $folder => $total) {
                $path = str_replace($realBase, '', (string) realpath($folder));
                $pathArr = explode('/', $path);
                if (isset($pathArr[1])) {
                    $folderName = $pathArr[1] . '/';
                    if (! isset($folderSimpleTotals[$folderName])) {
                        $folderSimpleTotals[$folderName] = 0;
                    }
                    $folderSimpleTotals[$folderName] += $total;
                    $strippedFolder = str_replace($this->basePath, '', $folder);
                    $this->addToOutput(sprintf("%d:\t %s\n", $total, $strippedFolder));
                }
            }
            $strippedRealBase = '/';
            $this->addToOutput(
                sprintf("\n--------------\nSummary: by root directory (%s)\n--------------\n", $strippedRealBase)
            );
            arsort($folderSimpleTotals);
            foreach ($folderSimpleTotals as $folder => $total) {
                $strippedFolder = str_replace($this->basePath, '', $folder);
                $this->addToOutput(sprintf("%d:\t %s\n", $total, $strippedFolder));
            }
            $this->addToOutput(sprintf("\n--------------\nTotal replacements: %d\n--------------\n", $totalSearches));
        }
        //add to total total
        $this->totalTotal += $totalSearches;

        //return total
        return $totalSearches;
    }

    //================================================
    // Doers
    //================================================

    /**
     * Searches all the files and creates the logs
     *
     * @return self
     */
    public function startSearchAndReplace()
    {
        $flatArray = $this->fileFinder->getFlatFileArray();
        foreach ($flatArray as $file) {
            $this->searchFileData($file);
        }
        if ($this->totalFound) {
            $msg = $this->totalFound . ' matches (' . $this->replacementType . ') for: ' . $this->logString;
            $this->addToOutput($msg);
        }
        if ($this->errorText !== '') {
            $this->addToOutput("\t Error-----" . $this->errorText);
        }

        return $this;
    }

    /**
     * THE KEY METHOD!
     * Searches data, replaces (if enabled) with given key, prepares log
     * @param string $file - e.g. /var/www/mysite.co.nz/mysite/code/Page.php
     */
    private function searchFileData($file)
    {
        $foundInLineCount = 0;
        $myReplacementKey = $this->replacementKey;
        $searchKey = preg_quote($this->searchKey, '/');
        if ($this->isReplacingEnabled) {
            //prerequisites for file and content ...
            if ($this->testMustContain($file) === false) {
                return;
            }
            if ($this->testFileNameRequirements($file) === false) {
                return;
            }

            $magicalData = [];
            $magicalData['classNameOfFile'] = $this->getClassNameOfFile($file);
            foreach ($this->magicReplacers as $magicReplacerFind => $magicReplacerReplaceVariable) {
                $myReplacementKey = str_replace(
                    $magicReplacerFind,
                    $magicalData[$magicReplacerReplaceVariable],
                    $myReplacementKey
                );
            }
            $oldFileContentArray = (array) file($file) ?? [];
            $newFileContentArray = [];
            $pattern = "/${searchKey}/U";
            if (! $this->caseSensitive) {
                $pattern = "/${searchKey}/Ui";
            }
            $foundCount = 0;
            $insidePreviousReplaceComment = false;
            $insideIgnoreArea = false;
            $completedTask = false;
            foreach ($oldFileContentArray as $oldLineContent) {
                $newLineContent = (string) $oldLineContent . '';

                if ($completedTask === false) {
                    $testLine = (string) trim((string) $oldLineContent);

                    //check if it is actually already replaced ...
                    if (strpos((string) $oldLineContent, $this->startMarker) !== false) {
                        $insidePreviousReplaceComment = true;
                    }
                    foreach ($this->ignoreFrom as $ignoreStarter) {
                        if (strpos((string) $testLine, $ignoreStarter) === 0) {
                            $insideIgnoreArea = true;
                        }
                    }
                    if ($insidePreviousReplaceComment || $insideIgnoreArea) {
                        //do nothing ...
                    } else {
                        $foundInLineCount = preg_match_all(
                            $pattern,
                            (string) $oldLineContent,
                            $matches,
                            PREG_PATTERN_ORDER
                        );
                        if ($foundInLineCount) {
                            if ($this->caseSensitive) {
                                if (strpos((string) $oldLineContent, (string) $this->searchKey) === false) {
                                    user_error('Regex found it, but phrase does not exist: ' . $this->searchKey);
                                }
                            } else {
                                if (stripos((string) $oldLineContent, $this->searchKey) === false) {
                                    user_error('Regex found it, but phrase does not exist: ' . $this->searchKey);
                                }
                            }
                            $foundCount += $foundInLineCount;
                            if ($this->isReplacingEnabled) {
                                $newLineContent = preg_replace($pattern, $myReplacementKey, (string) $oldLineContent);
                                if ($fullComment = $this->getFullComment()) {
                                    $newFileContentArray[] = $fullComment;
                                }
                            }
                        } else {
                            if ($this->caseSensitive) {
                                if (strpos((string) $oldLineContent, (string) $this->searchKey) !== false) {
                                    user_error('Should have found: ' . $this->searchKey);
                                }
                            } else {
                                if (stripos((string) $oldLineContent, (string) $this->searchKey) !== false) {
                                    user_error('Should have found: ' . $this->searchKey);
                                }
                            }
                        }
                    }
                    if (strpos((string) $oldLineContent, (string) $this->endMarker) !== false) {
                        $insidePreviousReplaceComment = false;
                    }
                    foreach ($this->ignoreUntil as $ignoreEnder) {
                        if (strpos((string) $testLine, (string) $ignoreEnder) === 0) {
                            $insideIgnoreArea = false;
                        }
                    }
                    if ($this->fileReplacementMaxCount > 0 && $foundCount >= $this->fileReplacementMaxCount) {
                        $completedTask = true;
                    }
                }

                $newFileContentArray[] = $newLineContent;
            }
            if ($foundCount) {
                $oldFileContent = implode($oldFileContentArray);
                $newFileContent = implode($newFileContentArray);
                if ($newFileContent !== $oldFileContent) {
                    $this->writeToFile($file, $newFileContent);

                    //stats
                    $this->totalFound += $foundInLineCount;
                    if (! isset($this->searchKeyTotals[$this->searchKey])) {
                        $this->searchKeyTotals[$this->searchKey] = 0;
                    }
                    $this->searchKeyTotals[$this->searchKey] += $foundCount;

                    if (! isset($this->folderTotals[dirname($file)])) {
                        $this->folderTotals[dirname($file)] = 0;
                    }
                    $this->folderTotals[dirname($file)] += $foundCount;

                    //log
                    $foundStr = "-- ${foundCount} x";
                    if ($this->fileReplacementMaxCount) {
                        $foundStr .= ' limited to ' . $this->fileReplacementMaxCount;
                    }
                    $this->appendToLog($file, $foundStr);
                } else {
                    $this->appendToLog(
                        $file,
                        '********** ERROR: NO REPLACEMENT DESPITE MATCHES - searched for: ' .
                        $pattern . ' and replaced with ' . $myReplacementKey . " \n"
                    );
                }
            }
        } else {
            $this->appendToLog($file, '********** ERROR: Replacement Text is not defined');
        }
    }

    /**
     * Writes new data (after the replacement) to file
     * @param string $file,
     * @param string $data
     */
    private function writeToFile($file, $data)
    {
        if (is_writable($file)) {
            $fp = fopen($file, 'w');
            if ($fp) {
                fwrite($fp, $data);
                fclose($fp);
            } else {
                user_error('Could not open ' . $file);
            }
        } else {
            user_error(
                "********** ERROR: Can not replace text. File ${file} is not writable.",
                "\nPlease make it writable\n"
            );
        }
    }

    /**
     * Appends log data to previous log data
     * @param string $file
     * @param string $matchStr
     */
    private function appendToLog($file, $matchStr)
    {
        if ($this->logString === '') {
            $this->logString = "'" . $this->searchKey . "'\n";
        }
        $file = basename($file);
        $this->logString .= "   ${matchStr} IN ${file}\n";
    }

    /**
     * returns full output
     * and clears it.
     * @return string
     */
    private function addToOutput($s)
    {
        $this->output .= $s;
    }

    private function getClassNameOfFile($filePath)
    {
        if (! self::$finder) {
            self::$finder = new FileNameToClass();
        }
        if (! isset(self::$class_name_cache[$filePath])) {
            $class = self::$finder->getClassNameFromFile($filePath);
            //see: https://stackoverflow.com/questions/7153000/get-class-name-from-file/44654073
            // $file = 'class.php'; # contains class Foo
            // $class = shell_exec("php -r \"include('$file'); echo end(get_declared_classes());\"");
            //see: https://stackoverflow.com/questions/7153000/get-class-name-from-file/44654073
            // $fp = fopen($filePath, 'r');
            // $class = $buffer = '';
            // $i = 0;
            // while (!$class) {
            //     if (feof($fp)) {
            //         break;
            //     }
            //
            //     $buffer .= fread($fp, 512);
            //     @$tokens = token_get_all($buffer);
            //
            //     if (strpos($buffer, '{') === false) continue;
            //
            //     for (;$i<count($tokens);$i++) {
            //         if ($tokens[$i][0] === T_CLASS) {
            //             for ($j=$i+1;$j<count($tokens);$j++) {
            //                 if ($tokens[$j] === '{') {
            //                     $class = $tokens[$i+2][1];
            //                     break 2;
            //                 }
            //             }
            //         }
            //     }
            // }
            self::$class_name_cache[$filePath] = $class;
        }
        return self::$class_name_cache[$filePath];
    }

    private function testMustContain($fileName)
    {
        if (is_array($this->ignoreFileIfFound) && count($this->ignoreFileIfFound)) {
            foreach ($this->ignoreFileIfFound as $ignoreString) {
                if ($this->hasStringPresentInFile($fileName, $ignoreString)) {
                    $this->appendToLog($fileName, '********** Ignoring file, as ignore string found: ' . $ignoreString);

                    return false;
                }
            }
        }
        return true;
    }

    private function testFileNameRequirements($fileName)
    {
        if (is_array($this->fileNameMustContain) && count($this->fileNameMustContain)) {
            $passed = false;
            $fileBaseName = basename($fileName);
            foreach ($this->fileNameMustContain as $fileNameMustContainString) {
                if (stripos($fileBaseName, $fileNameMustContainString) !== false) {
                    $passed = true;
                }
            }
            if ($passed === false) {
                $this->appendToLog(
                    $fileName,
                    "********** skipping file ('.${fileBaseName}.'), as it does not contain following: " .
                    implode(', ', $this->fileNameMustContain)
                );

                return false;
            }
        }

        return true;
    }

    private function hasStringPresentInFile(string $fileName, string $string): bool
    {
        // get the file contents, assuming the file to be readable (and exist)
        $contents = file_get_contents($fileName);
        if (strpos((string) $contents, (string) $string) !== false) {
            return true;
        }
        return false;
    }
}
