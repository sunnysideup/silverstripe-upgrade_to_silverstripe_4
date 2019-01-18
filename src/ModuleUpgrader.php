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
        if (!$this->locationOfUpgradeModule) {
            $this->locationOfUpgradeModule = dirname(__DIR__) .'/vendor/silverstripe/upgrader/bin/upgrade-code';
        }
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
     * A list of task groups
     *
     * @var array
     */
    protected $taskSteps = [
        's00' => 'Generic',
        's10' => 'Prepare Codebase',
        's20' => 'Upgrade Structure',
        's30' => 'Prepare Code',
        's40' => 'Upgrade Code',
        's50' => 'Upgrade Fixes',
        's60' => 'Check',
        's70' => 'Finalise',
        's99' => 'ERROR!'
    ];

    /**
     * An array of all the 'taskName's of the tasks that you wish to run during the execution of this upgrader task.
     * This array can be overriden in the example-index.php file that you create.
     * You can enter a full name space if you need to.
     * The final -x will be removed.  We add -1 or -2 to run the same task multiple times.
     *
     * @var array
     */
    protected $listOfTasks = [
        //Step1: Prepare
        'CheckThatFoldersAreReady' => [],
        'ResetWebRootDir-1' => [],

        'CheckoutDevMaster-1' => [],
        'FindFilesWithMoreThanOneClass' => [],
        'AddLegacyBranch' => [],
        'ResetWebRootDir-2' => [],

        'CheckoutDevMaster-2' => [],
        'AddUpgradeBranch' => [],
        'RemoveComposerRequirements' => [
            'package' => 'silverstripe/framework'
        ],
        'RecomposeHomeBrew' => [],
        // 'UpdateComposerRequirements-2' => [
        //     'package' => 'silverstripe/cms',
        //     'replacementPackage' => 'silverstripe/recipe-cms',
        //     'newVersion' => '~1'
        // ],
        'RemoveInstallerFolder' => [],
        'ResetWebRootDir-3' => [],

        //Step2: MoveToNewVersion
        'ComposerInstallProject' => [],

        //Step3: FixBeforeStart
        'ChangeEnvironment' => [],
        'MoveCodeToSRC' => [],
        'CreateClientFolder' => [],
        'SearchAndReplace' => [],
        'FixRequirements' => [],
        'UpperCaseFolderNamesForPSR4' => [],

        //Step4: CoreUpgrade
        'AddNamespace' => [],
        'Upgrade' => [],

        //Step5: FixUpgrade
        'InspectAPIChanges-1' => [],
        'AddTableNamePrivateStatic' => [],
        'DatabaseMigrationLegacyYML' => [],
        'Reorganise' => [],
        'UpdateComposerModuleType' => [],
        'AddVendorExposeDataToComposer' => [],
        'AddPSR4Autoloading' => [],
        'InspectAPIChanges-2' => [],
        // 'WebRootUpdate' => [],
        //step6: Check
        'ApplyPSR2' => [],
        'FinalDevBuild' => [],
        'RunImageTask' => [],
        'DoMigrateSiteTreeLinkingTask' => [],
        'FindFilesWithSimpleUseStatements' => [],
        //step7: Lock-in
        'FinaliseUpgradeWithMergeIntoMaster' => []
    ];

    protected $frameworkComposerRestraint = '^4.3';

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
     * are we upgrading a module or a whole project?
     * @var bool
     */
    protected $isModuleUpgrade = true;

    /**
    * The default namespace for all tasks
    * @var string
    */
    protected $defaultNamespaceForTasks = 'Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks';

    /**
     * if set to true it will run each step and then stop.
     * It was save the last step.
     * When your run it again, it will start on the next step.
     *
     * @var bool
     */
    protected $runInteractively = false;

    /**
     * start the upgrade sequence at a particular task
     * @var string
     */
    protected $startFrom = '';


    /**
     * end the upgrade sequence after a particular task
     * @var string
     */
    protected $endWith = '';


    /**
     * only run this task ...
     * @var string
     */
    protected $onlyRun = '';


    /**
     * finish the run with a merge into master.
     * @var boolean
     */
    protected $runIrreversibly = false;

    /**
     * Is this the last TASK we are running?
     * @var bool
     */
    private $isLastMethod = false;

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
    * e.g. install folder for package in SS3.
    * @var string
    */
    protected $packageFolderNameForInstall = '';

    /**
     * e.g. sunnysideup/my-cool-module
     * @var string
     */
    protected $vendorAndPackageFolderNameForInstall = '';

    /**
     *Name space for the modules package
     * @var string
     */
    protected $packageNamespace = '';

    /**
     * git link for the module in ssh form
     * e.g. git@github.com:sunnysideup/silverstripe-dynamiccache.git
     * @var string
     */
    protected $gitLink = '';

    /**
     * git link for the module in https form
     * e.g. https://github.com/sunnysideup/silverstripe-dynamiccache/
     * @var string
     */
    protected $gitLinkAsHTTPS = '';

    /**
     * git link for the module in raw https form
     * e.g. https://raw.githubusercontent.com/sunnysideup/silverstripe-dynamiccache/
     * @var string
     */
    protected $gitLinkAsRawHTTPS = '';

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
    protected $locationOfUpgradeModule = '';

    /**
     * @var string
     */
    private $logFileLocation = '';

    /**
     * Combination of the web dir root name and the above webRootDirLocation
     * @var string
     */
    private $webRootDirLocation = '';

    /**
     * Directory that holds the module
     * or project.
     *
     * This is an array because a project can hold more than one
     * folder (e.g. mysite or app and specialstuff)
     *
     * a module is only one folder
     *
     * @var array
     */
    private $moduleDirLocations = [];

    /**
     * returns an array of existing paths
     *
     * @return array
     */
    public function getExistingModuleDirLocations()
    {
        $array = [];
        foreach($this->moduleDirLocations as $location) {
            if($location = $this->checkIfPathExistsAndCleanItUp($location)) {
                $array[$location] = $location;
            }
        }

        return $array;
    }

    /**
     * returns path for module
     *
     * @return string
     */
    public function getExistingFirstModuleDirLocation()
    {
        $locations = array_values($this->getExistingModuleDirLocations());
        $firstLocation = array_shift($locations);

        return $firstLocation;
    }

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
     * @return array codedirlocation
     */
    public function findNameSpaceAndCodeDirs()
    {
        $codeDirs = [];
        $locations = $this->getExistingModuleDirLocations();
        foreach($locations as $location) {
            $codeDir = $this->findMyCodeDir($location);
            if($codeDir) {
                if($this->getIsModuleUpgrade()) {
                    $baseNameSpace = $this->getVendorNamespace().'\\'.$this->getPackageNamespace();
                } else {
                    $nameSpaceKey = ucwords(basename($location));
                    if($nameSpaceKey === 'App' || $nameSpaceKey === 'Mysite') {
                        $nameSpaceKey = $this->getPackageNamespace();
                    }
                    $baseNameSpace = $this->getVendorNamespace().'\\'.$nameSpaceKey;
                }
                $codeDirs[$baseNameSpace] = $codeDir;
            }
        }

        return $codeDirs;
    }

    public function findMyCodeDir($moduleDir)
    {
        if(file_exists($moduleDir)) {
            if (file_exists($moduleDir . '/code') && file_exists($moduleDir . '/src')) {
                user_error('There is a code and a src dir for '.$moduleDir, E_USER_NOTICE);
            }
            elseif (file_exists($moduleDir . '/src')) {
                return $moduleDir . '/code';
            }
            elseif (file_exists($moduleDir . '/src')) {
                return $moduleDir . '/src';
            } else {
                user_error('Can not find code/src dir for '.$moduleDir, E_USER_NOTICE);
            }
        }
    }

    public function getGitRootDir()
    {
        if($this->getIsModuleUpgrade()) {
            $location = $this->getExistingFirstModuleDirLocation();
        } else {
            $location = $this->getWebRootDirLocation();
        }

        return $location;
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

    public function createListOfTasks()
    {
        $html = '<h1>List of Tasks in run order</h1>';
        $count = 0;
        $totalCount = count($this->listOfTasks);
        $previousStep = '';
        foreach ($this->listOfTasks as $class => $params) {
            $properClass = current(explode('-', $class));
            $nameSpacesArray = explode('\\', $class);
            $shortClassCode = end($nameSpacesArray);
            if (! class_exists($properClass)) {
                $properClass = $this->defaultNamespaceForTasks.'\\'.$properClass;
            }
            if (class_exists($properClass)) {
                $count++;
                $runItNow = $this->shouldWeRunIt($shortClassCode);
                $params['taskName'] = $shortClassCode;
                $obj = $properClass::create($this, $params);
                if($obj->getTaskName()) {
                    $params['taskName'] = $obj->getTaskName();
                }
                $reflectionClass = new \ReflectionClass($properClass);
                $path = 'https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/';
                $path .=  str_replace('\\', '/', $reflectionClass->getName()).'.php';
                $path =  str_replace('Sunnysideup/UpgradeToSilverstripe4/', '', $path);
                $currentStepCode = $obj->getTaskStepCode();
                $currentStep = $obj->getTaskStep($currentStepCode);
                if($currentStepCode === 's00'){
                    //do nothing when it is an anytime step
                } else {
                    if($previousStep !== $currentStep) {
                        $html .= '<h2>'.$currentStep.'</h2>';
                    }
                    $previousStep = $currentStep;
                }
                $html .= '<h4>'.$count.' / '.$totalCount.': '.$obj->getTitle().'</h4>';
                $html .= '<p>'.$obj->getDescription().'<br />';
                $html .= '<strong>Code: </strong>'.$class;
                $html .= '<br /><strong>Class Name: </strong><a href="'.$path.'">'. $reflectionClass->getShortName() .'</a>';
                $html .= '</p>';
                $obj = $properClass::deleteTask($params);
            } else {
                user_error($properClass.' could not be found as class', E_USER_ERROR);
            }
        }
        $dir = __DIR__.'/../docs/en/';
        file_put_contents(
            $dir . '/AvailableTasks.md',
            $html
        );
    }
    /**
     * Starts the command line output and prints some opening information to the output
     * also initalises various environment variables
     */
    public function run()
    {
        $this->workOutMethodsToRun();
        $this->startPHP2CommandLine();
        for ($i = 0; $i < 500; $i++) {
            $this->colourPrint(
                '.',
                'light_red',
                5
            );
        }
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
                    $runItNow = $this->shouldWeRunIt($shortClassCode);
                    $params['taskName'] = $shortClassCode;
                    $obj = $properClass::create($this, $params);
                    if($obj->getTaskName()) {
                        $params['taskName'] = $obj->getTaskName();
                    }
                    $this->colourPrint('# --------------------', 'yellow', 3);
                    $this->colourPrint('# '.$obj->getTitle(). ' ('.$params['taskName'].')', 'yellow');
                    $this->colourPrint('# --------------------', 'yellow');
                    $this->colourPrint('# '.$obj->getDescriptionNice(), 'dark_grey');
                    $this->colourPrint('# --------------------', 'yellow');
                    if ($runItNow) {
                        $obj->run();
                    } else {
                        $this->colourPrint('# skipped', 'light_green');
                        $this->colourPrint('# --------------------', 'yellow');
                        //important!
                    }
                    $obj = $properClass::deleteTask($params);
                } else {
                    user_error($properClass.' could not be found as class. You can add namespacing to include your own classes.', E_USER_ERROR);
                }
                if($this->runInteractively) {
                    $this->setSessionValue('Completed', $class);
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

        if (isset($moduleDetails['GitLink'])) {
            $this->gitLink = $moduleDetails['GitLink'];
        } else {
            $this->gitLink = 'git@github.com:'.$this->vendorName.'/silverstripe-'.$this->packageName.'.git';
        }
        $this->gitLinkAsHTTPS = rtrim(str_replace('git@github.com:', 'https://github.com/', $this->gitLink), '.git');
        $this->gitLinkAsRawHTTPS = rtrim(str_replace('git@github.com:', 'https://raw.githubusercontent.com/', $this->gitLink), '.git');

        //packageFolderNameForInstall
        $jsonFile = $this->gitLinkAsRawHTTPS. '/master/composer.json';
        $json = file_get_contents($jsonFile);
        $array = json_decode($json, true);
        if (isset($array['extra']['installer-name'])) {
            $this->packageFolderNameForInstall = $array['extra']['installer-name'];
        } else {
            $this->packageFolderNameForInstall = $this->packageName;
        }
        if (isset($moduleDetails['PackageFolderNameForInstall'])) {
            $this->packageFolderNameForInstall = $moduleDetails['PackageFolderNameForInstall'];
        }

        //moduleDirLocation
        if($this->getIsModuleUpgrade()) {
            $this->moduleDirLocations = [
                $this->webRootDirLocation . '/' . $this->packageFolderNameForInstall
            ];
        } else {
            foreach($this->moduleDirLocations as $key => $location) {
                $this->moduleDirLocations[$key] = $this->webRootDirLocation . '/' . $location;
            }
        }


        //ss4 location
        if (isset($moduleDetails['VendorAndPackageFolderNameForInstall'])) {
            $this->vendorAndPackageFolderNameForInstall = $moduleDetails['VendorAndPackageFolderNameForInstall'];
        } else {
            $this->vendorAndPackageFolderNameForInstall = strtolower($this->vendorName . '/' . $this->packageName);
        }

        //UpgradeAsFork
        $this->upgradeAsFork = empty($moduleDetails['UpgradeAsFork']) ? false : true;

        //LogFileLocation
        $this->logFileLocation = '';
        if ($this->logFolderDirLocation) {
            $this->logFileLocation = $this->logFolderDirLocation.'/'.$this->packageName.'-upgrade-log.'.time().'.txt';
            $this->commandLineExec->setLogFileLocation($this->logFileLocation);
        } else {
            $this->commandLineExec->setLogFileLocation('');
        }


        //output the confirmation.
        $this->colourPrint('---------------------', 'light_cyan');
        $this->colourPrint('UPGRADE DETAILS', 'light_cyan');
        $this->colourPrint('---------------------', 'light_cyan');
        $this->colourPrint('- Vendor Name: '.$this->vendorName, 'light_cyan');
        $this->colourPrint('- Package Name: '.$this->packageName, 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Vendor Namespace: '.$this->vendorNamespace, 'light_cyan');
        $this->colourPrint('- Package Namespace: '.$this->packageNamespace, 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Module / Project Dir(s): '.implode(', ', $this->moduleDirLocations), 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Git and Composer Root Dir: '.$this->getGitRootDir(), 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Git Repository Link (SSH): '.$this->gitLink, 'light_cyan');
        $this->colourPrint('- Git Repository Link (HTTPS): '.$this->gitLinkAsHTTPS, 'light_cyan');
        $this->colourPrint('- Git Repository Link (RAW): '.$this->gitLinkAsRawHTTPS, 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Upgrade as Fork: '.($this->upgradeAsFork ? 'yes' : 'no'), 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Run Irreversibly: '.($this->runIrreversibly ? 'yes' : 'no'), 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Log File Location: '.($this->logFileLocation ? $this->logFileLocation : 'not logged'), 'light_cyan');
        $this->colourPrint('---------------------', 'light_cyan');
    }

    protected function workOutMethodsToRun()
    {
        if($this->runInteractively) {
            if($this->startFrom || $this->endWith || $this->onlyRun) {
                die('In interactive mode you can not set StartFrom / EndWith / OnlyRun.');
            } else {
                $lastMethod = $this->getSessionValue('Completed');
                if($lastMethod) {
                    $arrayKeys = array_keys($this->listOfTasks);
                    foreach($arrayKeys as $index => $key) {
                        if($key === $lastMethod) {
                            if(isset($this->listOfTasks[$arrayKeys[$index + 1]])) {
                                $this->onlyRun = $this->listOfTasks[$arrayKeys[$index + 1]];
                            } else {
                                $this->deleteSession();
                            }
                        }
                    }
                } else {
                    reset($this->listOfTasks);
                    key($this->listOfTasks);
                }
            }
        }
    }

    /**
     * start the method ...
     * - should we run it?
     *
     * @param  string $name whatever is listed in the listOfTasks
     * @return bool
     */
    protected function shouldWeRunIt($name) : bool
    {
        if ($this->onlyRun) {
            return $name === $this->onlyRun ? true : false;
        }
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

        //here we call the PHP2CommandLine

        return $runMe;
    }

    protected $sessionFileName = '.upgrade.session.for';

    protected function getSessionFileLocation()
    {
        $this->getAboveWebRootDirLocation().
        '/'.
        $this->sessionFileName.
        '.'.
        basename($this->getWebRootDirLocation());
    }

    protected function initSession()
    {
        if(! file_exists($this->getSessionFileName())) {
            $this->setSessionData([]);
        }
    }

    protected function deleteSession()
    {
        unlink($this->getSessionFileName());
    }

    protected function getSessionValue($key)
    {
        $this->initSession();
        $session = $this->getSessionData();
        if(isset($session[$key])) {
            return $session[$key];
        } else {
            return null;
        }
    }

    protected function getSessionData()
    {
        $this->initSession();
        $data = file_get_contents($this->getSessionFileName());

        return unserialize($data);
    }

    /**
     * @param array $session
     */
    protected function setSessionData($session)
    {
        $data = serialize($session);
        file_put_contents($this->getSessionFileName(), $data);
    }

    protected function setSessionValue($key, $value)
    {
        $session = $this->getSessionData();
        $session[$key] = $value;
        $this->setSessionData($session);
    }

}
