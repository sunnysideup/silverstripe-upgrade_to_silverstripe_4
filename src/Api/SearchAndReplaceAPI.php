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
    private $basePath                  = '';

    private $searchPath                = '';

    private $defaultIgnoreFolderArray  = [
        ".svn",
        ".git"
    ];

    private $ignoreFolderArray         = [];

    private $extensions                = ["php", "ss", "yml", "yaml", "json", "js"];

    private $findAllExts               = false;

    private $searchKey                 = '';

    private $replacementKey            = '';

    private $futureReplacementKey      = '';

    private $isReplacingEnabled        = false;

    private $replacementType           = '';

    private $caseSensitive             = true;

    private $logString                 = ''; //details of one search

    private $errorText                 = ''; //details of one search

    private $totalFound                = 0; //total matches in one search

    private $output                    = ''; //buffer of output, until it is retrieved

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
     *   Sets folders to ignore
     *   @param Array ignoreFolderArray
     *   @return none
     */
    public function setIgnoreFolderArray($ignoreFolderArray = [])
    {
        if($this->ignoreFolderArray === $ignoreFolderArray) {
            //do nothing
        } else {
            $this->ignoreFolderArray = $ignoreFolderArray;
            $this->resetFileCache();
        }
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
        if($oldIgnoreFolderArray !== $this->ignoreFolderArray) {
            $this->resetFileCache();
        }
    }

    /**
     * remove ignore folders
     */
    public function resetIgnoreFolderArray()
    {
        $this->ignoreFolderArray = [];
        $this->resetFileCache();
    }


    /**
     */
    public function setBasePath($pathLocation)
    {
        $this->basePath = $pathLocation;
        $this->resetFileCache();
    }


    //================================================
    // Setters Before Every Search
    //================================================


    /**
     */
    public function setSearchPath($pathLocation)
    {
        if($pathLocation !== $this->searchPath) {
            $this->searchPath = $pathLocation;
            $this->resetFileCache();
        }
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
    }

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
    }

    /**
     *   Sets key to replace searchKey with
     *   @param String $replacementKey
     */
    public function setReplacementKey($replacementKey)
    {
        $this->replacementKey     = $replacementKey;
        $this->isReplacingEnabled = true;
    }

    /**
     *   Sets key to replace searchKey with BUT only hypothetical
     * (no replacement takes place!)
     *   @param String $replacementKey
     */
    public function setFutureReplacementKey($replacementKey)
    {
        $this->futureReplacementKey = $replacementKey;
        $this->isReplacingEnabled   = false;
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
     * returns full output
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



    //================================================
    // Write to log while doing the searches
    //================================================

    /**
     * should be run at the end of an extension.
     */
    public function showFormattedSearchTotals($returnTotalFoundOnly = false)
    {
        $totalSearches = 0;
        foreach (self::$search_key_totals as $searchKey => $total) {
            $totalSearches += $total;
        }
        if ($returnTotalFoundOnly) {
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
    public function startSearching()
    {
        $flatArray = $this->getFlatFileArray();
        foreach ($flatArray as $location) {
            $this->searchFileData("$location");
        }
        if ($this->totalFound) {
            $this->addToOutput("".$this->totalFound." matches (".$this->replacementType.") for: ".$this->logString);
        }
        if ($this->errorText!= '') {
            $this->addToOutput("\t Error-----".$this->errorText);
        }
        $this->logString = "";
        $this->errorText = "";
        $this->totalFound = 0;
    }

    private function resetFileCache()
    {
        self::$file_array = null;
        self::$file_array = [];
        self::$flat_file_array = null;
        self::$flat_file_array = [];
        //cleanup other data
    }

    /**
     * array of all the files we are searching
     * @var array
     */
    private static $file_array = [];


    /**
     * loads all the applicable files
     * @param String $path (e.g. "." or "/var/www/mysite.co.nz")
     * @param Boolean $innerLoop - is the method calling itself???
     *
     *
     */
    private function getFileArray($path, $runningInnerLoop = false)
    {
        $key = str_replace(array("/"), "__", $path);
        if ($runningInnerLoop || !count(self::$file_array)) {
            $dir = opendir($path);
            while ($file = readdir($dir)) {
                if (($file == ".") || ($file == "..") || (__FILE__ == "$path/$file") || ($path == "." && basename(__FILE__) == $file)) {
                    continue;
                }
                //ignore hidden files and folders
                if (substr($file, 0, 1) == ".") {
                    continue;
                }
                //ignore folders with _manifest_exclude in them!
                if ($file == "_manifest_exclude") {
                    $this->ignoreFolderArray[] = $path;
                    unset(self::$file_array[$key]);
                    break;
                }
                if (filetype("$path/$file") == "dir") {
                    if (
                        (in_array($file, $this->ignoreFolderArray) && ($path == "." || $path == $this->searchPath)) ||
                        (in_array($path, $this->ignoreFolderArray))
                    ) {
                        continue;
                    }
                    $this->getFileArray("$path/$file", $runningInnerLoop = true); //recursive traversing here
                } elseif ($this->matchedExtension($file)) { //checks extension if we need to search this file
                    if (filesize("$path/$file")) {
                        self::$file_array[$key][] = "$path/$file"; //search file data
                    }
                }
            } //End of while
            closedir($dir);
        }
        return self::$file_array;
    }

    /**
     * Flattened array of files.
     * @var Array
     */
    private static $flat_file_array = [];

    private function getFlatFileArray()
    {
        if($this->searchPath) {
            if (count(self::$flat_file_array) === 0) {
                if(file_exists($this->searchPath)) {
                    if(is_file($this->searchPath)) {
                        self::$flat_file_arra = [
                            $this->searchPath
                        ];
                    } else {
                        $multiDimensionalArray = $this->getFileArray($this->basePath);
                        //flatten it!
                        self::$flat_file_array = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($multiDimensionalArray));
                    }
                } else {
                    user_error('Can not find: '.$this->searchPath);
                }
            }
        }
        return self::$flat_file_array;
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
        if ($this->findAllExts) {
            return true;
        } elseif (sizeof(array_keys($this->extensions, $this->findExtension($file)))==1) {
            return true;
        }
        return false;
    }

    /**
     * THE KEY METHOD!
     * Searches data, replaces (if enabled) with given key, prepares log
     * @param String $file - e.g. /var/www/mysite.co.nz/mysite/code/Page.php
     */
    private function searchFileData($file)
    {
        $searchKey  = preg_quote($this->searchKey, '/');
        if ($this->caseSensitive) {
            $pattern    = "/$searchKey/U";
        } else {
            $pattern    = "/$searchKey/Ui";
        }
        $subject = file_get_contents($file);
        $found = 0;
        $found = preg_match_all($pattern, $subject, $matches, PREG_PATTERN_ORDER);
        $this->totalFound +=$found;
        if ($found) {
            $foundStr = " x $found";
            if ($this->isReplacingEnabled) {
                if ($this->replacementKey) {
                    $outputStr = preg_replace($pattern, $this->replacementKey, $subject);
                    $foundStr = "-- Replaced in $found places";
                    $this->writeToFile($file, $outputStr);
                    $this->appendToLog($file, $foundStr, $this->replacementKey);
                } else {
                    $this->errorText .= "********** ERROR: Replacement Text is not defined\n";
                    $this->appendToLog($file, "********** ERROR: Replacement Text is not defined", $this->replacementKey);
                }
            } else {
                if ($this->futureReplacementKey) {
                    $this->appendToLog($file, $foundStr, $this->futureReplacementKey);
                } else {
                    $this->errorText .= "********** ERROR: FUTURE Replacement Text is not defined\n";
                    $this->appendToLog($file, "********** ERROR: FUTURE Replacement Text is not defined");
                }
            }
            if (!isset(self::$search_key_totals[$this->searchKey])) {
                self::$search_key_totals[$this->searchKey] = 0;
            }
            self::$search_key_totals[$this->searchKey] += $found;

            if (!isset(self::$folder_totals[dirname($file)])) {
                self::$folder_totals[dirname($file)] = 0;
            }
            self::$folder_totals[dirname($file)] += $found;
        } else {
            //$this->appendToLog($file, "No matching Found", $this->replacementKey);
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
            $this->errorText .= "********** ERROR: Can not replace text. File $file is not writable. \nPlease make it writable\n";
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
