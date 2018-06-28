<?php

namespace Sunnysideup\UpgradeToSilverstripe4;

use Sunnysideup\PHP2CommandLine\PHP2CommandLineSingleton;

/**
 * recompose (Mandatory, stop execution on failure)
 */
class ModuleUpgrader
{

    /**
     * Holds the only instance of me
     * @var null|ModuleUpgrader
     */
    private static $_singleton = null;

    /**
     * Create the only instance of me and return it
     * @return ModuleUpgrader
     */
    public static function create()
    {
        if (self::$_singleton === null) {
            self::$_singleton = new ModuleUpgrader();
        }
        return self::$_singleton;
    }

    /**
     * Starts the output to the commandline / browser
     */
    public function __construct()
    {
        $this->startPHP2CommandLine();
    }

    /**
     * Ends output to commandline / browser
     */
    public function __destruct()
    {
        $this->endPHP2CommandLine();
    }

    /**
     * creates magic getters and setters
     * if you call $this->getFooBar() then it will get the variable FooBar even if the method
     * getFooBar does not exist.
     *
     * if you call $this->setFooBar('hello') then it will set the variable FooBar even if the method
     * setFooBar does not exist.
     *
     * See: http://php.net/manual/en/language.oop5.overloading.php#object.call
     *
     * @param  string   $function name of the function
     * @param  array    $args     parameters provided to the getter / setter
     *
     * @return mixed|Sunnysideup\UpgradeToSilverstripe4\ModuleUpgrader
     */
    public function __call($function, $args)
    {
        $getOrSet = substr($function, 0, 3);
        if ($getOrSet === 'set' || $getOrSet === 'get') {
            $var = lcfirst(ltrim($function, $getOrSet));
            if (isset($this->$var)) {
                if ($getOrSet === 'get') {
                    if (strpos($var, 'DirLocation') !== false || strpos($var, 'FileLocation') !== false) {
                        return $this->checkIfPathExistsAndCleanItUp($this->$var);
                    } else {
                        return $this->$var;
                    }

                } elseif ($getOrSet === 'set') {
                    $this->$var = $args[0];

                    return $this;
                }
            } else {
                user_error('Fatal error: can not get/set variable in ModuleUpgrader::'.$var, E_USER_ERROR);
            }
        } else {
            user_error('Fatal error: Call to undefined method ModuleUpgrader::'.$function.'()', E_USER_ERROR);
        }
    }




    #########################################
    # TASKS
    #########################################



    /**
     * An array of all the 'TaskName's of the tasks that you wish to run during the execution of this upgrader task.
     * This array can be overriden in the example-index.php file that you create.
     * @var string[] of TaskName
     */
    protected $listOfTasks = [
        'ResetWebRootDir-1' => [],
        'AddLegacyBranch' => [],
        'ResetWebRootDir-2' => [],
        'AddUpgradeBranch' => [],
        'UpdateComposerRequirements-1' => [
            'Package' => 'silverstripe/framework',
            'NewVersion' => '~4.0'
        ],
        'Recompose' => [],
        'UpdateComposerRequirements-2' => [
            'Package' => 'silverstripe/cms',
            'ReplacementPackage' => 'silverstripe/recipe-cms',
            'NewVersion' => '1.1.2'
        ],
        'ResetWebRootDir-3' => [],
        'ComposerInstallProject' => [],
        'ChangeEnvironment' => [],
        'MoveCodeToSRC' => [],
        'SearchAndReplace' => [],
        'UpperCaseFolderNamesForPSR4' => [],
        'AddNamespace' => [],
        'Upgrade' => [],
        'InspectAPIChanges' => [],
        'Reorganise' => [],
        // 'WebRootUpdate' => []
    ];

    /**
     * Removes the given task from the list of tasks to execute
     * @param  string $s name of the task to remove
     * @return Sunnysideup\UpgradeToSilverstripe4\ModuleUpgrader for chaining
     */
    public function removeFromListOfTasks($s)
    {
        if ($key = $this->positionForTask($s) !== false) {
            unset($messages[$key]);
        }

        return $this;
    }

    /**
     * Inserts another task to the list of tasks at a given position in the order of execution, if it is set
     * TODO These parameter names need some more refining
     * @param string|array  $oneOrMoreTasks the tasks to be inserted
     * @param bool          $insertBeforeOrAfter If to insert before or after
     * @param bool          $isBefore
     *
     * @return Sunnysideup\UpgradeToSilverstripe4\ModuleUpgrader
     */
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
    * The default namespace for all tasks
    * @var string
    */
    protected $defaultNamespaceForTasks = 'Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks';

    /**
     * start the upgrade sequence at a particular task
     * @var string task to start the upgrade sequence from
     */
    protected $startFrom = '';


    /**
     * end the upgrade sequence after a particular task
     * @var string task to end the upgrade sequence on
     */
    protected $endWith = '';

    /**
     * Is this the last TASK we are running?
     * @var bool
     */
    protected $isLastMethod = false;

    /**
     * What is the index of given task within the sequence
     *
     * @param string $s name of the task to find
     *
     * @return mixed the key/index of task
     */
    protected function positionForTask($s)
    {
        return array_search($s, $this->listOfTasks);
    }

    /**
     * Set the command line exec to run immediately rather than outputting the bash script
     * @return bool
     */
    public function getRunImmediately()
    {
        return $this->commandLineExec->getRunImmediately();
    }

    /**
     * @param bool $b
     */
    public function setRunImmediately($b)
    {
        $this->commandLineExec->setRunImmediately($b);

        return $this;
    }

    /**
     * Whether execution should come to a halt when an error is reached
     * @return bool
     */
    public function getBreakOnAllErrors()
    {
        return $this->commandLineExec->getBreakOnAllErrors();
    }

    /**
     * @param bool $b
     */
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
     *          'GitLinkg' => 'git@github.com:foor/bar-1.git',
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
     * The rest can be deduced (theoretically)
     * @var array of array modules to upgrade
     */
    protected $arrayOfModules = [];

    /**
     * Appends the given module in the form of all its module data that has to be formatted in an array
     * to the array of modules that will be worked with during the upgrade procedure.
     *
     * @param array module data to append
     * @return ModuleUpgrader for chaining
     */
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
     * @var string branch name
     */
    protected $nameOfTempBranch = 'temp-upgradeto4-branch';

    /**
     * Name of module vendor
     * @var string
     */
    protected $vendorName = '';

    /**
     * module vendors namespace
     * @var string
     */
    protected $vendorNamespace = '';

    /**
     * Package name for the module
     * @var string
     */
    protected $packageName = '';

    /**
     *Name space for the modules package
     * @var string
     */
    protected $packageNamespace = '';

    /**
     * git link for the module
     * @var string
     */
    protected $gitLink = '';

    /**
     * Should the upgrade to this module create a fork
     * @var bool
     */
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

    //TODO double check descriptions for these variables as still rather ambiguous

    /**
     * The folder for storing the log file in
     * used in setting the php2 command line printer up
     * @var string
     */
    protected $logFolderDirLocation = '';

    /**
     * location of web root above module
     * @var string directory
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

    /**
     * Combination of the web dir root name and the above webRootDirLocation
     * @var string
     */
    protected $webRootDirLocation = '';

    /**
     * Directory that holds the module
     * @var string
     */
    protected $moduleDirLocation = '';

    ###############################
    # HELPERS
    ###############################


    /**
     *Reference to the commandline printer that outputs everything to the command line
     * @var Sunnysideup\UpgradeToSilverstripe4\Util\PHP2CommandLineSingleton|null
     */
    protected $commandLineExec = null;


    ###############################
    # USEFUL COMMANDS
    ###############################

    /**
     * Executes given operations on the PHP2CommandLineSingleton instance
     * Documentation for this can be found in the PHP2CommandLineSingleton module
     */
    public function execMe($newDir, $command, $comment, $alwaysRun = false)
    {
        return $this->commandLineExec->execMe($newDir, $command, $comment, $alwaysRun);
    }

    /**
     * Executes given operations on the PHP2CommandLineSingleton instance
     * Documentation for this can be found in the PHP2CommandLineSingleton module
     */
    public function colourPrint($mixedVar, $colour = 'dark_gray', $newLineCount = 1)
    {
        return $this->commandLineExec->colourPrint($mixedVar, $colour, $newLineCount);
    }

    /**
     * Locates the directory in which the code is kept within the module directory
     *
     * If it can be found returns the location otherwise it errors
     *
     * @return string codedirlocation
     */
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


    /**
     * Cleans an input string and returns a more natural human readable version
     * @param  string $str input string
     * @param  array  $noStrip
     * @return string cleaned string
     */
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


    /**
     * Starts the command line output and prints some opening information to the output
     * also initalises various environment variables
     */
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
                $nameSpacesArray = explode('\\', $class);
                $shortClassCode = end($nameSpacesArray);
                if (! class_exists($properClass)) {
                    $properClass = $this->defaultNamespaceForTasks.'\\'.$properClass;
                }
                if (class_exists($properClass)) {
                    if ($this->startMethod($shortClassCode)) {
                        $params['TaskName'] = $shortClassCode;
                        $obj = $properClass::create($this, $params);
                        $obj->run();
                        //important!
                        $obj = $properClass::delete($params);
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
        $this->commandLineExec = PHP2CommandLineSingleton::create();
    }

    /**
     * deconstructs Command Line
     * important as this outputs the whole thing
     */
    protected function endPHP2CommandLine()
    {
        if ($this->commandLineExec !== null) {
            $this->commandLineExec = PHP2CommandLineSingleton::delete();
        }
    }

    /**
     * Loads in and sets all the meta data for a module from the inputed array
     * @param array $moduleDetails
     */
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