<?php

namespace Sunnysideup\UpgradeToSilverstripe4;

use Sunnysideup\PHP2CommandLine\PHP2CommandLineSingleton;

/**
 * recompose (Mandatory, stop execution on failure)
 */
class MetaUpgrader
{


    /**
     * only instance of me
     * @var MetaUpgrader
     */
    private static $_singleton = null;

    /**
     * only instance of me
     * @return MetaUpgrader
     */
    public static function create()
    {
        if (self::$_singleton === null) {
            self::$_singleton = new MetaUpgrader();
        }
        return self::$_singleton;
    }


    public function __construct()
    {
        $this->startPHP2CommandLine();
    }


    public function __destruct()
    {
        $this->endPHP2CommandLine();
    }

    function __call($function , $args) {
        $getOrSet = substr($function, 0, 3);
        if($getOrSet === 'set' || $getOrSet === 'get' ) {
            $var = lcfirst(ltrim($function, $getOrSet));
            if(isset($this->$var)) {
                if ($getOrSet === 'get') {
                    if(strpos($var, 'DirLocation') !== false || strpos($var, 'FileLocation') !== false) {
                        return $this->checkIfPathExistsAndCleanItUp($this->$var);
                    } else {
                        return $this->$var;
                    }
                }
                else if ($getOrSet === 'set') {
                    $this->$var= $args[0];
                    return $this;
                }
            } else {
                user_error ('Fatal error: can not get/set variable in MetaUpgrader::'.$var, E_USER_ERROR);
            }
        } else {
            user_error ('Fatal error: Call to undefined method MetaUpgrader::'.$function(), E_USER_ERROR);
        }
    }




    #########################################
    # TASKS
    #########################################



    /**
     * start the upgrade sequence at a particular method
     * @var string
     */
    protected $listOfTasks = [
        'ResetWebRootDir-1' => [],
        'AddUpgradeBranch' => [],
        'UpdateComposerRequirements-1' => [
            'Package' => 'silverstripe/framework',
            'NewVersion' => '~4.0'
        ],
        'UpdateComposerRequirements-2' => [
            'Package' => 'silverstripe/cms',
            'ReplacementPackage' => 'silverstripe/recipe-cms',
            'NewVersion' => '1.1.0'
        ],
        'Recompose' => [],

        'ResetWebRootDir-2' => [],
        'ComposerInstallProject' => [],
        // 'ChangeEnvironment' => [],
        'UpperCaseFolderNamesForPSR4' => [],
        'SearchAndReplace' => [],
        'AddNamespace' => [],
        'Upgrade' => [],
        'InspectAPIChanges' => [],
        'Reorganise' => [],
        // 'WebRootUpdate' => []
    ];

    public function removeFromListOfTasks($s)
    {
        if ($key = $this->positionForTask($s) !== false) {
            unset($messages[$key]);
        }

        return $this;
    }

    public function addToListOfTasks($oneOrMoreTasks, $insertBeforeOrAfter, $isBefore)
    {
        if (! is_array($oneOrMoreTasks)) {
            $oneOrMoreTasks = [$oneOrMoreTasks];
        }
        foreach ($this->listOfTasks as $key => $task) {
            if ($task === $insertBeforeOrAfter) {
                if ($isBefore) {
                    $pos = $key - 1;
                }
                array_splice(
                    $this->listOfTasks,
                    $pos,
                    0,
                    $oneOrMoreTasks
                );
            }
        }
        return $this;
    }

    /**
    * end the upgrade sequence after a particular method
    * @var string
    */
    protected $defaultNamespaceForTasks = 'Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks';

    /**
     * start the upgrade sequence at a particular method
     * @var string
     */
    protected $startFrom = '';


    /**
     * end the upgrade sequence after a particular method
     * @var string
     */
    protected $endWith = '';

    protected $isLastMethod = false;



    protected function positionForTask($s)
    {
        return array_search($s, $this->listOfTasks);
    }


    public function getRunImmediately()
    {
        return $this->commandLineExec->getRunImmediately();
    }

    public function setRunImmediately($b)
    {
        $this->commandLineExec->setRunImmediately($b);

        return $this;

    }

    public function getBreakOnAllErrors()
    {
        return $this->commandLineExec->getBreakOnAllErrors();
    }

    public function setBreakOnAllErrors($b)
    {
        $this->commandLineExec->setBreakOnAllErrors($b);

        return $this;
    }

    #########################################
    # MODULES
    #########################################


    /**
     * specified like this:
     *      [
     *          'VendorName' => 'A',
     *          'VendorNamespace' => 'A',
     *          'PackageName' => 'Package1',
     *          'PackageNamespace' => 'Package1',
     *          'GitLink' => 'git@github.com:foor/bar-1.git',
     *          'UpgradeAsFork' => false
     *      ],
     *      [
     *          'VendorName' => 'A',
     *          'VendorNamespace' => 'A',
     *          'PackageName' => 'Package2',
     *          'PackageNamespace' => 'Package2',
     *          'GitLink' => 'git@github.com:foor/bar-2.git',
     *          'UpgradeAsFork' => false
     *      ],
     * required are:
     * - VendorName
     * - PacakageName
     * The rest can be deducted (theoretically)
     * @var array
     */
    protected $arrayOfModules = [];

    public function addModule($a)
    {
        $this->arrayOfModules[] = $a;

        return $this;
    }







    #########################################
    # VENDOR / PACKAGE / GIT DETAILS
    #########################################



    /**
     * name of the branch created to do the upgrade
     * @var string
     */
    protected $nameOfTempBranch = 'temp-upgradeto4-branch';

    protected $vendorName = '';

    protected $vendorNamespace = '';

    protected $packageName = '';

    protected $packageNamespace = '';

    protected $gitLink = '';

    protected $upgradeAsFork = false;




    #########################################
    # COMPOSER
    #########################################


    /**
     *
     * e.g. COMPOSER_HOME="/home/UserName"
     *
     * @var string
     */
    protected $composerEnvironmentVars = '';





    #########################################
    # LOCATIONS
    #########################################






    /**
     *
     * @var string
     */
    protected $logFolderDirLocation = '';

    /**
     * @var string
     */
    protected $aboveWebRootDirLocation = '/var/www';


    /**
     * @var string
     */
    protected $webRootName = 'upgradeto4';




    /**
     * //e.g. 'upgrade-code'
     * //e.g. '~/.composer/vendor/bin/upgrade-code'
     * //e.g. '/var/www/silverstripe-upgrade_to_silverstripe_4/vendor/silverstripe/upgrader/bin/upgrade-code'
     * @var string
     */
    protected $locationOfUpgradeModule = 'upgrade-code';

    protected $logFileLocation = '';

    protected $webRootDirLocation = '';

    protected $moduleDirLocation = '';



    ###############################
    # HELPERS
    ###############################


    /**
     *
     * @var Sunnysideup\UpgradeToSilverstripe4\Util\PHP2CommandLineSingleton|null
     */
    protected $commandLineExec = null;


    ###############################
    # USEFUL COMMANDS
    ###############################


    public function execMe($newDir, $command, $comment, $alwaysRun = false)
    {
        return $this->commandLineExec->execMe($newDir, $command, $comment, $alwaysRun);
    }

    public function colourPrint($mixedVar, $colour, $newLineCount = 1)
    {
        return $this->commandLineExec->colourPrint($mixedVar, $colour, $newLineCount);
    }

    public function findCodeDir()
    {
        $codeDir = '';
        if (file_exists($this->moduleDirLocation . '/code')) {
            $codeDir = $this->moduleDirLocation . '/code';
        } elseif (file_exists($this->moduleDirLocation . '/src')) {
            $codeDir = $this->moduleDirLocation . '/src';
        } else {
            user_error('Can not find code dir for '.$this->moduleDirLocation, E_USER_NOTICE);
            return;
        }

        return $codeDir;
    }



    public function camelCase($str, array $noStrip = [])
    {
        $str = str_replace('-', ' ', $str);
        $str = str_replace('_', ' ', $str);
        // non-alpha and non-numeric characters become spaces
        $str = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $str);
        $str = trim($str);
        // uppercase the first character of each word
        $str = ucwords($str);
        $str = str_replace(" ", "", $str);

        return $str;
    }


    /**
     * returns path in a consistent format
     * e.g. /var/www
     *
     * @param  string $path
     *
     * @return string
     */
    public function checkIfPathExistsAndCleanItUp($path)
    {
        $originalPath = $path;
        $path = str_replace('///', '/', $path);
        $path = str_replace('//', '/', $path);
        if (file_exists($path)) {
            $path = realpath($path);
        }
        $path = rtrim($path, '/');

        return $path;
    }



    ###############################
    # RUN
    ###############################



    public function run()
    {
        $this->startPHP2CommandLine();

        //Init UTIL and helper objects
        $this->colourPrint(
            '===================== START ======================',
            'light_red',
            5
        );

        $this->aboveWebRootDirLocation = $this->checkIfPathExistsAndCleanItUp($this->aboveWebRootDirLocation);
        $this->webRootDirLocation = $this->checkIfPathExistsAndCleanItUp($this->aboveWebRootDirLocation.'/'.$this->webRootName);
        foreach ($this->arrayOfModules as $counter => $moduleDetails) {
            $this->loadVarsForModule($moduleDetails);

            foreach ($this->listOfTasks as $class => $params) {
                $properClass = current(explode('-', $class));
                $nameSpacesArray = explode('\\',$class);
                $shortClassCode = end($nameSpacesArray);
                if (! class_exists($properClass)) {
                    $properClass = $this->defaultNamespaceForTasks.'\\'.$properClass;
                }
                if (class_exists($properClass)) {
                    if ($this->startMethod($shortClassCode)) {
                        $params['TaskName'] = $shortClassCode;
                        $obj = $properClass::create($this, $params);
                        $obj->run();
                    }
                } else {
                    user_error($properClass.' could not be found as class', E_USER_ERROR);
                }
            }
        }
        $this->colourPrint(
            '===================== END =======================',
            'light_red',
            5
        );
        $this->endPHP2CommandLine();
    }

    /**
     * Starts the logger. Extra checking may be put in here to see if you
     * want to start the logger or not in different scenarios.
     *
     * For now it defaults to always existing
     * @return [type] [description]
     */
    protected function startPHP2CommandLine()
    {
        if($this->commandLineExec === null) {
            $this->commandLineExec = PHP2CommandLineSingleton::create();
        }
    }

    /**
     * deconstructs Command Line
     * important as this outputs the whole thing
     */
    protected function endPHP2CommandLine()
    {
        if($this->commandLineExec !== null) {
            $this->commandLineExec = PHP2CommandLineSingleton::delete();
        }
    }


    protected function loadVarsForModule($moduleDetails)
    {
        //VendorName
        $this->vendorName = $moduleDetails['VendorName'];

        //VendorNamespace
        if (isset($moduleDetails['VendorNamespace'])) {
            $this->vendorNamespace = $moduleDetails['VendorNamespace'];
        } else {
            $this->vendorNamespace = $this->camelCase($this->vendorName);
        }

        //PackageName
        $this->packageName = $moduleDetails['PackageName'];

        //PackageNamespace
        if (isset($moduleDetails['PackageNamespace'])) {
            $this->packageNamespace = $moduleDetails['PackageNamespace'];
        } else {
            $this->packageNamespace = $this->camelCase($this->packageName);
        }

        //GitLink
        $this->moduleDirLocation = $this->webRootDirLocation . '/' . $this->packageName;
        if (isset($moduleDetails['GitLink'])) {
            $this->gitLink = $moduleDetails['GitLink'];
        } else {
            $this->gitLink = 'git@github.com:'.$this->vendorName.'/silverstripe-'.$this->packageName;
        }

        //UpgradeAsFork
        $this->upgradeAsFork = empty($moduleDetails['UpgradeAsFork']) ? false : true;

        //LogFileLocation
        $this->logFileLocation = '';
        if ($this->logFolderDirLocation) {
            $this->logFileLocation = $this->logFolderDirLocation.'/'.$this->packageName.'-upgrade-log.'.time().'.txt';
        }
        $this->commandLineExec->setLogFileLocation($this->logFileLocation);


        //output the confirmation.
        $this->colourPrint('---------------------', 'light_cyan');
        $this->colourPrint('UPGRADE DETAILS', 'light_cyan');
        $this->colourPrint('---------------------', 'light_cyan');
        $this->colourPrint('Vendor Name: '.$this->vendorName, 'light_cyan');
        $this->colourPrint('Vendor Namespace: '.$this->vendorNamespace, 'light_cyan');
        $this->colourPrint('Package Name: '.$this->packageName, 'light_cyan');
        $this->colourPrint('Package Namespace: '.$this->packageNamespace, 'light_cyan');
        $this->colourPrint('Module Dir: '.$this->moduleDirLocation, 'light_cyan');
        $this->colourPrint('Git Repository Link: '.$this->gitLink, 'light_cyan');
        $this->colourPrint('Upgrade as Fork: '.($this->upgradeAsFork ? 'yes' : 'no'), 'light_cyan');
        $this->colourPrint('Log File Location: '.($this->logFileLocation ? $this->logFileLocation : 'not logged'), 'light_cyan');
        $this->colourPrint('---------------------', 'light_cyan');
    }

    /**
     * start the method ...
     * - should we run it?
     * - print the starting header
     *
     * @param  string $name whatever is listed in the listOfTasks
     * @return bool
     */
    protected function startMethod($name) : bool
    {
        if ($this->isLastMethod) {
            $runMe = false;
        } else {
            if ($this->startFrom) {
                if ($name === $this->startFrom) {
                    $this->startFrom = '';
                }
            }
            if ($this->endWith) {
                if ($name === $this->endWith) {
                    $this->isLastMethod = true;
                }
            }
            $runMe = $this->startFrom ? false : true;
        }
        $this->colourPrint('# --------------------', 'yellow', 3);
        $this->colourPrint('# '.$name, 'yellow');
        $this->colourPrint('# --------------------', 'yellow');
        if (! $runMe) {
            $this->colourPrint('# skipped', 'light_green');
        }

        //here we call the PHP2CommandLine

        return $runMe;
    }


}
