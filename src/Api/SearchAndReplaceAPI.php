<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Api;

/**
* Class : TextSearch
*
* @author  :  MA Razzaque Rupom <rupom_315@yahoo.com>, <rupom.bd@gmail.com>
*             Moderator, phpResource Group(http://groups.yahoo.com/group/phpresource/)
*             URL: http://rupom.wordpress.com
*
* HEAVILY MODIFIED BY SUNNY SIDE UP
*
* @version :  1.0
* Date     :  06/25/2006
* Purpose  :  Searching and replacing text within files of specified path
*/

class SearchAndReplaceAPI
{

    //generic search settings

    private $debug                     = false;

    private $basePath                  = '';

    private $isReplacingEnabled        = false;

    //specific search settings

    private $searchKey                 = '';

    private $replacementKey            = '';

    private $comment                   = '';

    private $startMarker               =  '### @@@@ START REPLACEMENT @@@@ ###';

    private $endMarker                 =  '### @@@@ STOP REPLACEMENT @@@@ ###';

    private $replacementHeader         =  '';

    private $replacementType           = '';

    private $caseSensitive             = true;

    // files

    private $fileFinder                = null;

    //stats and reporting

    private $logString                 = ''; //details of one search

    private $errorText                 = ''; //details of one search

    private $totalFound                = 0; //total matches in one search

    private $output                    = ''; //buffer of output, until it is retrieved


    // static counts

    private static $search_key_totals  = [];

    private static $folder_totals      = [];

    private static $total_total        = 0;

    public function __construct($basePath = '')
    {
        $this->basePath = $basePath;
        $this->fileFinder = new FindFiles($basePath);
    }



    //================================================
    // Setters Before Run
    //================================================


    /**
     *   @return this
     */
    public function setDebug($b)
    {
        $this->debug = $b;

        return $this;
    }

    /**
     *   @return this
     */
    public function setIsReplacingEnabled($b)
    {
        $this->isReplacingEnabled = $b;

        return $this;
    }


    /**
     *   Sets folders to ignore
     *   @param Array ignoreFolderArray
     *   @return none
     */
    public function setIgnoreFolderArray($ignoreFolderArray = [])
    {
        $this->fileFinder->setIgnoreFolderArray($ignoreFolderArray);

        return $this;
    }

    /**
     *   Sets folders to ignore
     *   @param Array ignoreFolderArray
     *   @return none
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


    /**
     */
    public function setBasePath($pathLocation)
    {
        $this->basePath = $pathLocation;
        $this->fileFinder->setBasePath($pathLocation);

        return $this;
    }

    /**
     */
    public function setSearchPath($pathLocation)
    {
        $this->fileFinder->setSearchPath($pathLocation);

        return $this;
    }

    /**
     *   Sets extensions to look
     *   @param Array extensions
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

    //================================================
    // Setters Before Every Search
    //================================================



    /**
     * Sets search key and case sensitivity
     * @param string $searchKey,
     * @param bool $caseSensitivity
     */
    public function setSearchKey($searchKey, $caseSensitive = false, $replacementType)
    {
        $this->searchKey        = $searchKey;
        $this->caseSensitive    = $caseSensitive;
        $this->replacementType  = $replacementType;
        //reset comment
        $this->comment          = '';

        return $this;
    }

    /**
     *   Sets key to replace searchKey with
     *   @param String $replacementKey
     */
    public function setReplacementKey($replacementKey)
    {
        $this->replacementKey     = $replacementKey;
        $this->isReplacingEnabled = true;

        return $this;
    }

    /**
     *   Sets a comment to go with the replacement.
     *   @param String $comment
     *
     */
    public function setComment($comment)
    {
        $this->comment            = $comment;

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
            PHP_EOL.
                '/**'.PHP_EOL.
                '  * '.$this->startMarker.PHP_EOL;
            if ($this->replacementHeader) {
                $string .= '  * WHY: '.$this->replacementHeader.PHP_EOL;
            }
            $string .=
                '  * OLD: '.$this->searchKey.' ('.($this->caseSensitive ? 'case sensitive' : 'ignore case').')' . PHP_EOL.
                '  * NEW: '.$this->replacementKey.($this->replacementType ? ' ('.$this->replacementType.')' : '').PHP_EOL.
                '  * EXP: '.$this->comment.PHP_EOL.
                '  * '.$this->endMarker.PHP_EOL.
                '  */'.
                PHP_EOL;
        }

        return $string;
    }


    //================================================
    // Get FINAL output
    //================================================


    /**
     * returns full output
     * and clears it.
     * @return string
     */
    public function getOutput()
    {
        $output = $this->output;
        $this->output = "";

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
        $this->logString = "";

        return $logString;
    }


    /**
     * returns the TOTAL TOTAL number of
     * found replacements
     */
    public function getTotalTotalSearches()
    {
        return self::$total_total;
    }

    /**
     * should be run at the end of an extension.
     */
    public function showFormattedSearchTotals($suppressOutput = false)
    {
        $totalSearches = 0;
        foreach (self::$search_key_totals as $searchKey => $total) {
            $totalSearches += $total;
        }
        if ($suppressOutput) {
            //do nothing
        } else {
            $flatArray = $this->fileFinder->getFlatFileArray();
            if($flatArray && ! is_array($flatArray)) {
                $this->addToOutput("\n".$flatArray."\n");
            } else {
                $this->addToOutput("\n------------------------------------\nFiles Searched\n------------------------------------\n");
                foreach ($flatArray as $file) {
                    $strippedFile = str_replace($this->basePath, "", $file);
                    $this->addToOutput($strippedFile."\n");
                }
            }
            $folderSimpleTotals = [];
            $realBase = realpath($this->basePath);
            $this->addToOutput("\n------------------------------------\nSummary: by search key\n------------------------------------\n");
            arsort(self::$search_key_totals);
            foreach (self::$search_key_totals as $searchKey => $total) {
                $this->addToOutput(sprintf("%d:\t %s\n", $total, $searchKey));
            }
            $this->addToOutput("\n------------------------------------\nSummary: by directory\n------------------------------------\n");
            arsort(self::$folder_totals);
            foreach (self::$folder_totals as $folder => $total) {
                $path = str_replace($realBase, "", realpath($folder));
                $pathArr = explode("/", $path);
                if (isset($pathArr[1])) {
                    $folderName = $pathArr[1]."/";
                    if (!isset($folderSimpleTotals[$folderName])) {
                        $folderSimpleTotals[$folderName] = 0;
                    }
                    $folderSimpleTotals[$folderName] += $total;
                    $strippedFolder = str_replace($this->basePath, "", $folder);
                    $this->addToOutput(sprintf("%d:\t %s\n", $total, $strippedFolder));
                }
            }
            $strippedRealBase = "/";
            $this->addToOutput(sprintf("\n------------------------------------\nSummary: by root directory (%s)\n------------------------------------\n", $strippedRealBase));
            arsort($folderSimpleTotals);
            foreach ($folderSimpleTotals as $folder => $total) {
                $strippedFolder = str_replace($this->basePath, "", $folder);
                $this->addToOutput(sprintf("%d:\t %s\n", $total, $strippedFolder));
            }
            $this->addToOutput(sprintf("\n------------------------------------\nTotal replacements: %d\n------------------------------------\n", $totalSearches));
        }
        //add to total total
        self::$total_total += $totalSearches;

        //return total
        return $totalSearches;
    }



    //================================================
    // Doers
    //================================================


    /**
     * Searches all the files and creates the logs
     * @param $path to search
     * @return none
     */
    public function startSearchAndReplace()
    {
        $flatArray = $this->fileFinder->getFlatFileArray();
        foreach ($flatArray as $file) {
            $this->searchFileData($file);
        }
        if ($this->totalFound) {
            $this->addToOutput("".$this->totalFound." matches (".$this->replacementType.") for: ".$this->logString);
        }
        if ($this->errorText!= '') {
            $this->addToOutput("\t Error-----".$this->errorText);
        }

        return $this;
    }


    /**
     * THE KEY METHOD!
     * Searches data, replaces (if enabled) with given key, prepares log
     * @param String $file - e.g. /var/www/mysite.co.nz/mysite/code/Page.php
     */
    private function searchFileData($file)
    {
        $searchKey  = preg_quote($this->searchKey, '/');
        if ($this->replacementKey) {
            $oldFileContentArray = file($file);
            $newFileContentArray = [];
            $pattern = "/$searchKey/U";
            if (! $this->caseSensitive) {
                $pattern = "/$searchKey/Ui";
            }
            $foundCount = 0;
            $insidePreviousReplaceComment = false;
            foreach ($oldFileContentArray as $key => $oldLineContent) {
                $newLineContent = $oldLineContent;

                //check if it is actually already replaced ...
                if (strpos($oldLineContent, $this->startMarker) !== false) {
                    $insidePreviousReplaceComment = true;
                }
                if (strpos($oldLineContent, $this->endMarker) !== false) {
                    $insidePreviousReplaceComment = false;
                }
                if (! $insidePreviousReplaceComment) {
                    $foundInLineCount = preg_match_all($pattern, $oldLineContent, $matches, PREG_PATTERN_ORDER);
                    if ($foundInLineCount) {
                        if ($this->caseSensitive) {
                            if (strpos($oldLineContent, $this->searchKey) === false) {
                                user_error('Regex found it, but phrase does not exist: '.$this->searchKey);
                            }
                        } else {
                            if (stripos($oldLineContent, $this->searchKey) === false) {
                                user_error('Regex found it, but phrase does not exist: '.$this->searchKey);
                            }
                        }
                        $foundCount += $foundInLineCount;
                        if ($this->isReplacingEnabled) {
                            $newLineContent = preg_replace($pattern, $this->replacementKey, $oldLineContent);
                            if ($fullComment = $this->getFullComment()) {
                                $newFileContentArray[] = $fullComment;
                            }
                        }
                    } else {
                        $hasError = false;
                        if ($this->caseSensitive) {
                            if (strpos($oldLineContent, $this->searchKey) !== false) {
                                user_error('Should have found: '.$this->searchKey);
                            }
                        } else {
                            if (stripos($oldLineContent, $this->searchKey) !== false) {
                                user_error('Should have found: '.$this->searchKey);
                            }
                        }
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
                    if (!isset(self::$search_key_totals[$this->searchKey])) {
                        self::$search_key_totals[$this->searchKey] = 0;
                    }
                    self::$search_key_totals[$this->searchKey] += $foundCount;

                    if (!isset(self::$folder_totals[dirname($file)])) {
                        self::$folder_totals[dirname($file)] = 0;
                    }
                    self::$folder_totals[dirname($file)] += $foundCount;

                    //log
                    $foundStr = "-- $foundCount x";
                    $this->appendToLog($file, $foundStr);
                } else {
                    $this->appendToLog($file, "********** NO REPLACEMENT DESPITE MATCHES - searched for: ".$pattern." AND REPLACED WITH ".$this->replacementKey." \n");
                }
            }
        } else {
            $this->appendToLog($file, "********** ERROR: Replacement Text is not defined");
        }
    }

    /**
     * Writes new data (after the replacement) to file
     * @param $file, $data
     * @return none
     */
    private function writeToFile($file, $data)
    {
        if (is_writable($file)) {
            $fp = fopen($file, "w");
            fwrite($fp, $data);
            fclose($fp);
        } else {
            user_error("********** ERROR: Can not replace text. File $file is not writable. \nPlease make it writable\n");
        }
    }



    /**
    * Appends log data to previous log data
    * @param string $file
    * @param string $matchStr
    *
    * @return none
    */
    private function appendToLog($file, $matchStr)
    {
        if ($this->logString == '') {
            $this->logString = "'".$this->searchKey."'\n";
        }
        $file = basename($file);
        $this->logString .= "   $matchStr IN $file\n";
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
}
