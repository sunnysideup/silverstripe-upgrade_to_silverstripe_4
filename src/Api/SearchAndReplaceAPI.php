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

    private $searchPath                = '';

    private $defaultIgnoreFolderArray  = [
        ".svn",
        ".git"
    ];

    private $ignoreFolderArray         = [];

    private $extensions                = ["php", "ss", "yml", "yaml", "json", "js", "md"];

    private $findAllExts               = false;

    private $isReplacingEnabled        = false;

    //specific search settings

    private $searchKey                 = '';

    private $replacementKey            = '';

    private $comment                   = '';

    private $replacementType           = '';


    private $caseSensitive             = true;

    // files

    /**
    * array of all the files we are searching
    * @var array
    */
    private $fileArray                 = [];

    private $flatFileArray             = [];

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
        if ($this->ignoreFolderArray === $ignoreFolderArray) {
            //do nothing
        } else {
            $this->ignoreFolderArray = $ignoreFolderArray;
            $this->resetFileCache();
        }

        return $this;
    }

    /**
     *   Sets folders to ignore
     *   @param Array ignoreFolderArray
     *   @return none
     */
    public function addToIgnoreFolderArray($ignoreFolderArray = [])
    {
        $oldIgnoreFolderArray = $this->ignoreFolderArray;
        $this->ignoreFolderArray = array_unique(
            array_merge(
                $ignoreFolderArray,
                $this->defaultIgnoreFolderArray
            )
        );
        if ($oldIgnoreFolderArray !== $this->ignoreFolderArray) {
            $this->resetFileCache();
        }

        return $this;
    }

    /**
     * remove ignore folders
     */
    public function resetIgnoreFolderArray()
    {
        $this->ignoreFolderArray = [];
        $this->resetFileCache();

        return $this;
    }


    /**
     */
    public function setBasePath($pathLocation)
    {
        $this->basePath = $pathLocation;
        $this->resetFileCache();

        return $this;
    }

    /**
     */
    public function setSearchPath($pathLocation)
    {
        if ($pathLocation !== $this->searchPath) {
            $this->searchPath = $pathLocation;
            $this->resetFileCache();
        }

        return $this;
    }

    /**
     *   Sets extensions to look
     *   @param Array extensions
     */
    public function setExtensions($extensions = [])
    {
        $this->extensions = $extensions;
        if (count($this->extensions)) {
            $this->findAllExts = false;
        } else {
            $this->findAllExts = true;
        }
        $this->resetFileCache();

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
            $flatArray = $this->getFlatFileArray();
            $this->addToOutput("\n------------------------------------\nFiles Searched\n------------------------------------\n");
            foreach ($flatArray as $file) {
                $strippedFile = str_replace($this->basePath, "", $file);
                $this->addToOutput($strippedFile."\n");
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
        $flatArray = $this->getFlatFileArray();
        foreach ($flatArray as $file) {
            $this->searchFileData($file);
        }
        if ($this->totalFound) {
            $this->addToOutput("".$this->totalFound." matches (".$this->replacementType.") for: ".$this->logString);
        }
        if ($this->errorText!= '') {
            $this->addToOutput("\t Error-----".$this->errorText);
        }
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
            if ($this->caseSensitive) {
                $pattern    = "/$searchKey/U";
            } else {
                $pattern    = "/$searchKey/Ui";
            }
            $foundCount = 0;
            foreach($oldFileContentArray as $key => $oldLineContent)  {
                $newLineContent = $oldLineContent;
                $foundInLineCount = preg_match_all($pattern, $oldLineContent, $matches, PREG_PATTERN_ORDER);
                if($foundInLineCount) {
                    $foundCount += $foundInLineCount;
                    if ($this->isReplacingEnabled) {
                        $newLineContent = preg_replace($pattern, $this->replacementKey, $oldLineContent);
                        if($oldLineContent !== $newLineContent) {
                            if($this->comment) {
                                $newFileContentArray[] = $this->comment;
                            }
                        }
                    }
                }
                $newFileContentArray[] = $newLineContent;
            }
            if($foundCount) {
                $oldFileContent = implode($oldFileContentArray);
                $newFileContent = implode($newFileContentArray);
                if ($newFileContent === $oldFileContent) {
                    $this->writeToFile($file, $newFileContent);

                    //stats
                    $this->totalFound += $foundInLine;
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




    //FIND FILES

    private function resetFileCache()
    {
        $this->fileArray = null;
        $this->fileArray = [];
        $this->flatFileArray = null;
        $this->flatFileArray = [];
        //cleanup other data
    }


    /**
     * loads all the applicable files
     * @param String $path (e.g. "." or "/var/www/mysite.co.nz")
     * @param Boolean $innerLoop - is the method calling itself???
     *
     *
     */
    private function getFileArray($path, $runningInnerLoop = false)
    {
        if ($runningInnerLoop || !count($this->fileArray)) {
            $dir = opendir($path);
            while ($file = readdir($dir)) {
                $fullPath = $path.'/'.$file;
                if (($file == ".") || ($file == "..") || (__FILE__ == $fullPath) || ($path == "." && basename(__FILE__) == $file)) {
                    continue;
                }
                //ignore hidden files and folders
                if (substr($file, 0, 1) == ".") {
                    continue;
                }
                //ignore folders with _manifest_exclude in them!
                if ($file == "_manifest_exclude") {
                    $this->ignoreFolderArray[] = $path;
                    unset($this->fileArray[$path]);
                    break;
                }
                if (filetype($fullPath) == "dir") {
                    if (
                        (in_array($file, $this->ignoreFolderArray) && ($path == "." || $path == $this->searchPath)) ||
                        (in_array($path, $this->ignoreFolderArray))
                    ) {
                        continue;
                    }
                    $this->getFileArray($fullPath, $runningInnerLoop = true); //recursive traversing here
                } elseif ($this->matchedExtension($file)) { //checks extension if we need to search this file
                    if (filesize($fullPath)) {
                        $this->fileArray[$path][] = $fullPath; //search file data
                    }
                }
            } //End of while
            closedir($dir);
        }
        return $this->fileArray;
    }

    private function getFlatFileArray()
    {
        if (count($this->flatFileArray) === 0) {
            if ($this->searchPath) {
                if (file_exists($this->searchPath)) {
                    if (is_file($this->searchPath)) {
                        $this->flatFileArray = [
                            $this->searchPath
                        ];
                    } else {
                        $multiDimensionalArray = $this->getFileArray($this->basePath);
                        //flatten it!
                        $this->flatFileArray = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($multiDimensionalArray));
                    }
                } else {
                    $this->addToOutput("\n".'SKIPPED: can not find: '.$this->searchPath."\n");
                }
            }
        }
        return $this->flatFileArray;
    }

    /**
     * Finds extension of a file
     * @param filename
     * @return file extension
     */
    private function findExtension($file)
    {
        $fileArray = explode(".", $file);

        return array_pop($fileArray);
    }

    /**
     * Checks if a file extension is one of the extensions we are going to search
     * @param String $filename
     * @return Boolean
     */
    private function matchedExtension($file)
    {
        $fileExtension = $this->findExtension($file);
        if ($this->findAllExts) {
            return true;
        } elseif (in_array('*', $this->extensions)) {
            return true;
        } elseif (in_array($fileExtension, $this->extensions)) {
            return true;
        }
        return false;
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
