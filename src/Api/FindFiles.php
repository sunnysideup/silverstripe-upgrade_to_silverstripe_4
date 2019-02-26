<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Api;

class FindFiles
{

    //generic search settings


    private $basePath                  = '';

    private $searchPath                = '';

    private $relevantFolders           = [];

    private $defaultIgnoreFolderArray  = [
        ".svn",
        ".git"
    ];

    private $ignoreFolderArray         = [];

    private $extensions                = ["php", "ss", "yml", "yaml", "json", "js", "md"];

    private $findAllExts               = false;


    // files

    /**
    * array of all the files we are searching
    * @var array
    */
    private $fileArray                 = [];

    private $flatFileArray             = [];

    public function __construct($basePath = '')
    {
        $this->basePath = $basePath;
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
     *   Sets extensions to look
     *   @param bool $boolean
     */
    public function setFindAllExts($boolean = true)
    {
        $this->findAllExts = $boolean;

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

    /**
     * string is error!
     * @return array|string
     */
    public function getFlatFileArray()
    {
        if (count($this->flatFileArray) === 0) {
            $myArray = [];
            if ($this->searchPath) {
                if (file_exists($this->searchPath)) {
                    if (is_file($this->searchPath)) {
                        $this->flatFileArray = [$this->searchPath];
                    } else {
                        $multiDimensionalArray = $this->getFileArray($this->basePath);
                        foreach($multiDimensionalArray as $folder => $arrayOfFiles) {
                            if(count($arrayOfFiles)) {
                                $this->relevantFolders[$folder] = $folder;
                            }
                            foreach($arrayOfFiles as $file) {
                                $myArray[$file] = $file;
                            }
                        }
                        // //flatten it!
                        // $this->flatFileArray = new \RecursiveIteratorIterator(
                        //     new \RecursiveArrayIterator($multiDimensionalArray)
                        // );
                        // print_r($this->flatFileArray);
                    }
                } else {
                    return 'SKIPPED: can not find: '.$this->searchPath."\n";
                }
            }
            $this->flatFileArray = array_values($myArray);
        }

        return $this->flatFileArray;
    }


    /**
     * loads all the applicable files
     * @param String $path (e.g. "." or "/var/www/mysite.co.nz")
     * @param Boolean $innerLoop - is the method calling itself???
     *
     *
     */
    protected function getFileArray($path, $runningInnerLoop = false)
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




    //FIND FILES

    protected function resetFileCache()
    {
        $this->fileArray = null;
        $this->fileArray = [];
        $this->flatFileArray = null;
        $this->flatFileArray = [];
        //cleanup other data
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

}
