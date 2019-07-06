<?php

namespace Sunnysideup\UpgradeToSilverstripe4;

use Sunnysideup\PHP2CommandLine\PHP2CommandLineSingleton;

class ModuleUpgrader
{
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
        's99' => 'ERROR!',
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
        'CreatePublicFolder' => [],
        'AddTableName' => [],
        'ChangeControllerInitToProtected' => [],
        // 'AddTableNamePrivateStatic' => [],
        'RemoveComposerRequirements' => [
            'package' => 'silverstripe/framework',
        ],
        'RecomposeHomeBrew' => [],
        'UpdateComposerRequirements' => [],
        'RemoveInstallerFolder' => [],
        'ResetWebRootDir-3' => [],

        //Step2: MoveToNewVersion
        'ComposerInstallProject' => [],
        'Recompose' => [],

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
        'AddPSR4Autoloading' => [],

        //Step5: FixUpgrade
        'FixBadUseStatements' => [],
        'InspectAPIChanges-1' => [],
        'DatabaseMigrationLegacyYML' => [],
        'Reorganise' => [],
        'UpdateComposerModuleType' => [],
        'AddVendorExposeDataToComposer' => [],
        'InspectAPIChanges-2' => [],
        // 'WebRootUpdate' => [],
        //step6: Check
        'ApplyPSR2' => [],
        'FinalDevBuild' => [],
        'RunImageTask' => [],
        'DoMigrateSiteTreeLinkingTask' => [],
        'FindFilesWithSimpleUseStatements' => [],
        //step7: Lock-in
        'FinaliseUpgradeWithMergeIntoMaster' => [],
    ];

    protected $frameworkComposerRestraint = '^4.4';

    /**
     * Should the session details be deleted before we start?
     * @var bool
     */
    protected $restartSession = false;

    /**
     * Should the session details be deleted before we start?
     * @var bool
     */
    protected $runLastOneAgain = false;

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
     * Show ALL the information or just a little bit.
     * @var bool
     */
    protected $verbose = false;

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
    protected $locationOfThisUpgrader = '';

    /**
     * //e.g. 'upgrade-code'
     * //e.g. '~/.composer/vendor/bin/upgrade-code'
     * //e.g. '/var/www/silverstripe-upgrade_to_silverstripe_4/vendor/silverstripe/upgrader/bin/upgrade-code'
     * @var string
     */
    protected $locationOfSSUpgradeModule = '';

    ###############################
    # HELPERS
    ###############################

    /**
     *Reference to the commandline printer that outputs everything to the command line
     * @var PHP2CommandLineSingleton
     */
    protected $commandLineExec = null;

    /**
     * does the exec output Key Notes?
     * @var bool
     */
    protected $makeKeyNotes = false;

    /**
     * @var string
     */
    protected $originComposerFileLocation = '';

    protected $sessionFileName = 'Session_For';

    /**
     * Holds the only instance of me
     * @var ModuleUpgrader|null
     */
    private static $_singleton = null;

    /**
     * Is this the last TASK we are running?
     * @var bool
     */
    private $lastMethodHasBeenRun = false;

    /**
     * @var string
     */
    private $logFileLocation = '';

    /**
     * Combination of the web dir root name and the aboveWebRootDirLocation
     * @var string
     */
    private $webRootDirLocation = '';

    /**
     * Combination of the web dir root name and the aboveWebRootDirLocation
     * @var string
     */
    private $themeDirLocation = '';

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
     * Starts the output to the commandline / browser
     */
    public function __construct()
    {
        $this->startPHP2CommandLine();
        if (! $this->locationOfThisUpgrader) {
            $this->locationOfThisUpgrader = dirname(__DIR__);
        }
        if (! $this->locationOfSSUpgradeModule) {
            $this->locationOfSSUpgradeModule = $this->locationOfThisUpgrader .
                '/vendor/silverstripe/upgrader/bin/upgrade-code';
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
            if (property_exists($this, $var)) {
                if ($getOrSet === 'get') {
                    if (strpos($var, 'DirLocation') !== false || strpos($var, 'FileLocation') !== false) {
                        return $this->checkIfPathExistsAndCleanItUp($this->{$var}, true);
                    }
                    return $this->{$var};
                } elseif ($getOrSet === 'set') {
                    $this->{$var} = $args[0];

                    return $this;
                }
            } else {
                user_error('Fatal error: can not get/set variable in ModuleUpgrader::' . $var, E_USER_ERROR);
            }
        } else {
            user_error('Fatal error: Call to undefined method ModuleUpgrader::' . $function . '()', E_USER_ERROR);
        }
    }

    /**
     * Create the only instance of me and return it
     * @return ModuleUpgrader
     */
    public static function create()
    {
        if (self::$_singleton === null) {
            self::$_singleton = new self();
        }
        return self::$_singleton;
    }

    /**
     * Removes the given task from the list of tasks to execute
     * @param  string $taskName name of the task
     * @param  string $variableName name of the task
     * @param  mixed $variableValue name of the task
     *
     * @return ModuleUpgrader
     */
    public function setVariableForTask($taskName, $variableName, $variableValue)
    {
        $key = $this->positionForTask($taskName);
        if ($key !== false) {
            $this->listOfTasks[$taskName][$variableName] = $variableValue;
        } else {
            user_error('Could not find ' . $taskName . '. Choose from ' . implode(', ', array_keys($this->listOfTasks)));
        }

        return $this;
    }

    /**
     * Removes the given task from the list of tasks to execute
     * @param  string $s name of the task to remove
     *
     * @return ModuleUpgrader
     */
    public function removeFromListOfTasks($s)
    {
        $key = $this->positionForTask($s);
        if ($key !== false) {
            unset($this->listOfTasks[$key]);
        } else {
            user_error('Removing non existent task ' . $key . '. Choose from ' . implode(', ', $this->listOfTasks));
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
     * @return ModuleUpgrader
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
     * Whether execution should come to a halt when an error is reached
     * @return bool
     */
    public function getIsProjectUpgrade()
    {
        return $this->isModuleUpgrade ? false : true;
    }

    /**
     * @param bool $b
     */
    public function setBreakOnAllErrors($b)
    {
        $this->commandLineExec->setBreakOnAllErrors($b);

        return $this;
    }

    /**
     * Appends the given module in the form of all its module data that has to be formatted in an array
     * to the array of modules that will be worked with during the upgrade procedure.
     *
     * @param array $a data to append
     * @return ModuleUpgrader
     */
    public function addModule($a)
    {
        $this->arrayOfModules[] = $a;

        return $this;
    }

    /**
     * returns an array of existing paths
     *
     * @return array
     */
    public function getExistingModuleDirLocations()
    {
        $array = [];
        foreach ($this->moduleDirLocations as $location) {
            if ($location = $this->checkIfPathExistsAndCleanItUp($location)) {
                $array[$location] = $location;
            }
        }
        if (count($array) === 0) {
            if ($this->getIsModuleUpgrade()) {
            } else {
                user_error(
                    'You need to set moduleDirLocations (setModuleDirLocations)
                    as there are currently none.'
                );
            }
        }

        return $array;
    }

    public function getExistingModuleDirLocationsWithThemeFolders()
    {
        $array = $this->getExistingModuleDirLocations();
        if ($this->themeDirLocation) {
            $array[$this->themeDirLocation] = $this->themeDirLocation;
        }
    }

    /**
     * returns path for module
     *
     * @return string
     */
    public function getExistingFirstModuleDirLocation()
    {
        $locations = array_values($this->getExistingModuleDirLocations());
        return array_shift($locations);
    }

    ###############################
    # USEFUL COMMANDS
    ###############################

    /**
     * Executes given operations on the PHP2CommandLineSingleton instance
     * Documentation for this can be found in the PHP2CommandLineSingleton module
     */
    public function execMe(
        $newDir,
        $command,
        $comment,
        $alwaysRun = false,
        $keyNotesLogFileLocation = ''
    ) {
        if ($keyNotesLogFileLocation) {
            $this->commandLineExec
                ->setMakeKeyNotes(true)
                ->setKeyNotesFileLocation($keyNotesLogFileLocation);
        } else {
            $this->commandLineExec
                ->setMakeKeyNotes(false);
        }
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
        foreach ($locations as $location) {
            $codeDir = $this->findMyCodeDir($location);
            if ($codeDir) {
                if ($this->getIsModuleUpgrade()) {
                    $baseNameSpace = $this->getVendorNamespace() . '\\' . $this->getPackageNamespace() . '\\';
                } else {
                    $nameSpaceKey = ucwords(basename($location));
                    if (strtolower($nameSpaceKey) === 'app' || strtolower($nameSpaceKey) === 'mysite') {
                        $nameSpaceKey = $this->getPackageNamespace();
                    }
                    $baseNameSpace = $this->getVendorNamespace() . '\\' . $nameSpaceKey . '\\';
                }
                $codeDirs[$baseNameSpace] = $codeDir;
            }
        }

        return $codeDirs;
    }

    public function findMyCodeDir($moduleDir)
    {
        if (file_exists($moduleDir)) {
            $test1 = $moduleDir . '/code';
            $test2 = $moduleDir . '/src';
            if (file_exists($test1) && file_exists($test2)) {
                user_error('There is a code and a src dir for ' . $moduleDir, E_USER_NOTICE);
            } elseif (file_exists($test1)) {
                return $moduleDir . '/code';
            } elseif (file_exists($test2)) {
                return $moduleDir . '/src';
            } else {
                user_error('Can not find code/src dir for ' . $moduleDir, E_USER_NOTICE);
            }
        }
    }

    public function getGitRootDir()
    {
        if ($this->getIsModuleUpgrade()) {
            $location = $this->getExistingFirstModuleDirLocation();
            if (! $location) {
                return $this->moduleDirLocations[0];
            }
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
        $str = preg_replace('/[^a-z0-9' . implode('', $noStrip) . ']+/i', ' ', $str);
        $str = trim($str);
        // uppercase the first character of each word
        $str = ucwords($str);
        return str_replace(' ', '', $str);
    }

    /**
     * returns path in a consistent format
     * e.g. /var/www
     *
     * @param  string $path
     *
     * @return string | null
     */
    public function checkIfPathExistsAndCleanItUp($path, $returnEvenIfItDoesNotExists = false)
    {
        $originalPath = $path;
        $path = str_replace('///', '/', $path);
        $path = str_replace('//', '/', $path);
        if (file_exists($path)) {
            $path = realpath($path);
        }
        if (file_exists($path) || $returnEvenIfItDoesNotExists) {
            return rtrim($path, '/');
        }
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
                $properClass = $this->defaultNamespaceForTasks . '\\' . $properClass;
            }
            if (class_exists($properClass)) {
                $count++;
                $runItNow = $this->shouldWeRunIt($shortClassCode);
                $params['taskName'] = $shortClassCode;
                $obj = $properClass::create($this, $params);
                if ($obj->getTaskName()) {
                    $params['taskName'] = $obj->getTaskName();
                }
                $reflectionClass = new \ReflectionClass($properClass);
                $path = 'https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/';
                $path .= str_replace('\\', '/', $reflectionClass->getName()) . '.php';
                $path = str_replace('Sunnysideup/UpgradeToSilverstripe4/', '', $path);
                $currentStepCode = $obj->getTaskStepCode();
                $currentStep = $obj->getTaskStep($currentStepCode);
                if ($currentStepCode === 's00') {
                    //do nothing when it is an anytime step
                } else {
                    if ($previousStep !== $currentStep) {
                        $html .= '<h2>' . $currentStep . '</h2>';
                    }
                    $previousStep = $currentStep;
                }
                $html .= '<h4>' . $count . ': ' . $obj->getTitle() . '</h4>';
                $html .= '<p>' . $obj->getDescription() . '<br />';
                $html .= '<strong>Code: </strong>' . $class;
                $html .= '<br /><strong>Class Name: </strong><a href="' . $path . '">' . $reflectionClass->getShortName() . '</a>';
                $html .= '</p>';
                $obj = $properClass::deleteTask($params);
            } else {
                user_error($properClass . ' could not be found as class', E_USER_ERROR);
            }
        }
        $dir = __DIR__ . '/../docs/en/';
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
        $this->loadNextStepInstructions();
        $this->aboveWebRootDirLocation = $this->checkIfPathExistsAndCleanItUp($this->aboveWebRootDirLocation);
        $this->webRootDirLocation = $this->checkIfPathExistsAndCleanItUp($this->aboveWebRootDirLocation . '/' . $this->webRootName, true);
        $this->themeDirLocation = $this->checkIfPathExistsAndCleanItUp($this->webRootDirLocation . '/themes', true);
        foreach ($this->arrayOfModules as $counter => $moduleDetails) {
            $this->loadVarsForModule($moduleDetails);
            $this->workOutMethodsToRun();
            $this->printVarsForModule($moduleDetails);
            foreach ($this->listOfTasks as $class => $params) {
                $properClass = current(explode('-', $class));
                $nameSpacesArray = explode('\\', $class);
                $shortClassCode = end($nameSpacesArray);
                if (! class_exists($properClass)) {
                    $properClass = $this->defaultNamespaceForTasks . '\\' . $properClass;
                }
                if (class_exists($properClass)) {
                    $runItNow = $this->shouldWeRunIt($shortClassCode);
                    $params['taskName'] = $shortClassCode;
                    $obj = $properClass::create($this, $params);
                    if ($obj->getTaskName()) {
                        $params['taskName'] = $obj->getTaskName();
                    }
                    if ($runItNow) {
                        $this->colourPrint('# --------------------', 'yellow', 3);
                        $this->colourPrint('# ' . $obj->getTitle() . ' (' . $params['taskName'] . ')', 'yellow');
                        $this->colourPrint('# --------------------', 'yellow');
                        $this->colourPrint('# ' . $obj->getDescriptionNice(), 'dark_grey');
                        $this->colourPrint('# --------------------', 'dark_grey');
                        $obj->run();
                        if ($this->runInteractively) {
                            $this->setSessionValue('Completed', $class);
                        }
                    } else {
                        if (! $this->runInteractively) {
                            $this->colourPrint('# --------------------', 'yellow', 3);
                            $this->colourPrint('# ' . $obj->getTitle() . ' (' . $params['taskName'] . ')', 'yellow');
                            $this->colourPrint('# --------------------', 'yellow');
                            $this->colourPrint('# skipped', 'yellow');
                            $this->colourPrint('# --------------------', 'yellow');
                        }
                    }
                    $obj = $properClass::deleteTask($params);
                } else {
                    user_error($properClass . ' could not be found as class. You can add namespacing to include your own classes.', E_USER_ERROR);
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
     * What is the index of given task within the sequence
     *
     * @param string $s name of the task to find
     *
     * @return mixed the key/index of task
     */
    protected function positionForTask($s)
    {
        if (isset($this->listOfTasks[$s])) {
            return $s;
        }
        return array_search($s, $this->listOfTasks, true);
    }

    protected function loadNextStepInstructions()
    {
        if (PHP_SAPI === 'cli') {
            $this->restartSession = isset($argv[1]) && $argv[1] === 'restart';
        } else {
            $this->restartSession = isset($_GET['restart']);
        }
        //todo next / previous / etc...
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

        //Is Module Upgrade
        //do this first as a lot of other functions rely on it ...
        $this->isModuleUpgrade = isset($moduleDetails['IsModuleUpgrade']) ? $moduleDetails['IsModuleUpgrade'] : true;

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
            $this->gitLink = 'git@github.com:' . $this->vendorName . '/silverstripe-' . $this->packageName . '.git';
        }
        //see: https://stackoverflow.com/questions/5573334/remove-a-part-of-a-string-but-only-when-it-is-at-the-end-of-the-string
        $gitLinkWithoutExtension = preg_replace('/' . preg_quote('.git', '/') . '$/', '', $this->gitLink);
        $this->gitLinkAsHTTPS = str_replace('git@github.com:', 'https://github.com/', $gitLinkWithoutExtension);
        $this->gitLinkAsRawHTTPS = str_replace('git@github.com:', 'https://raw.githubusercontent.com/', $gitLinkWithoutExtension);

        //Origin Composer FileLocation
        $this->originComposerFileLocation = isset($moduleDetails['OriginComposerFileLocation']) ? $moduleDetails['OriginComposerFileLocation'] : '';
        if ($this->packageFolderNameForInstall) {
            //do nothing
        } else {
            if ($this->getSessionValue('PackageFolderNameForInstall')) {
                $this->packageFolderNameForInstall = $this->getSessionValue('PackageFolderNameForInstall');
            } else {
                if (! $this->originComposerFileLocation) {
                    $this->originComposerFileLocation = $this->gitLinkAsRawHTTPS . '/master/composer.json';
                }
                if ($this->URLExists($this->originComposerFileLocation)) {
                    $json = file_get_contents($this->originComposerFileLocation);
                    $array = json_decode($json, true);
                    if (isset($array['extra']['installer-name'])) {
                        $this->packageFolderNameForInstall = $array['extra']['installer-name'];
                    } else {
                        if ($this->isModuleUpgrade) {
                            $this->packageFolderNameForInstall = $this->packageName;
                        } else {
                            $this->packageFolderNameForInstall = 'mysite';
                        }
                    }
                    if (isset($moduleDetails['PackageFolderNameForInstall'])) {
                        $this->packageFolderNameForInstall = $moduleDetails['PackageFolderNameForInstall'];
                    }
                }
                //user_error('You need to set originComposerFileLocation using ->setOriginComposerFileLocation. Could not find: '.$this->originComposerFileLocation);
            }
            $this->setSessionValue('PackageFolderNameForInstall', $this->packageFolderNameForInstall);
        }

        //moduleDirLocation
        if ($this->isModuleUpgrade) {
            $this->moduleDirLocations = [
                $this->webRootDirLocation . '/' . $this->packageFolderNameForInstall,
            ];
            $this->themeDirLocation = null;
        } else {
            if (! count($this->moduleDirLocations)) {
                $this->moduleDirLocations[] = $this->webRootDirLocation . '/mysite';
                $this->moduleDirLocations[] = $this->webRootDirLocation . '/app';
            } else {
                foreach ($this->moduleDirLocations as $key => $location) {
                    $this->moduleDirLocations[$key] = $this->webRootDirLocation . '/' . $location;
                }
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
            $this->logFileLocation = $this->logFolderDirLocation . '/' . $this->packageName . '-upgrade-log.' . time() . '.txt';
            $this->commandLineExec->setLogFileLocation($this->logFileLocation);
        } else {
            $this->commandLineExec->setLogFileLocation('');
        }

        if ($this->restartSession) {
            $this->deleteSession();
        }
    }

    protected function printVarsForModule($moduleDetails)
    {
        //output the confirmation.
        $this->colourPrint('---------------------', 'light_cyan');
        $this->colourPrint('UPGRADE DETAILS', 'light_cyan');
        $this->colourPrint('---------------------', 'light_cyan');
        $this->colourPrint('- Type: ' . ($this->getIsModuleUpgrade() ? 'module' : 'project'), 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Vendor Name: ' . $this->vendorName, 'light_cyan');
        $this->colourPrint('- Package Name: ' . $this->packageName, 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Upgrade as Fork: ' . ($this->upgradeAsFork ? 'yes' : 'no'), 'light_cyan');
        $this->colourPrint('- Run Interactively: ' . ($this->runInteractively ? 'yes' : 'no'), 'light_cyan');
        $this->colourPrint('- Run Irreversibly: ' . ($this->runIrreversibly ? 'yes' : 'no'), 'light_cyan');
        $this->colourPrint('- Is Module Upgrade: ' . ($this->isModuleUpgrade ? 'yes' : 'no'), 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Vendor Namespace: ' . $this->vendorNamespace, 'light_cyan');
        $this->colourPrint('- Package Namespace: ' . $this->packageNamespace, 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Upgrade Dir (root of install): ' . $this->getWebRootDirLocation(), 'light_cyan');
        $this->colourPrint('- Package Folder Name For Install: ' . $this->packageFolderNameForInstall, 'light_cyan');
        $this->colourPrint('- Module / Project Dir(s): ' . implode(', ', $this->moduleDirLocations), 'light_cyan');
        $this->colourPrint('- Theme Dir: ' . $this->themeDirLocation, 'light_cyan');
        $this->colourPrint('- Git and Composer Root Dir: ' . $this->getGitRootDir(), 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Git Repository Link (SSH): ' . $this->gitLink, 'light_cyan');
        $this->colourPrint('- Git Repository Link (HTTPS): ' . $this->gitLinkAsHTTPS, 'light_cyan');
        $this->colourPrint('- Git Repository Link (RAW): ' . $this->gitLinkAsRawHTTPS, 'light_cyan');
        $this->colourPrint('- Origin composer file location: ' . $this->originComposerFileLocation, 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Session file: ' . $this->getSessionFileLocation(), 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Last Step: ' . ($this->getSessionValue('Completed') ?: 'not set'), 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Log File Location: ' . ($this->logFileLocation ?: 'not logged'), 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- List of Steps: ' . $this->newLine() . ' -' . implode($this->newLine() . '    -', array_keys($this->listOfTasks)), 'light_cyan');
        $this->colourPrint('---------------------', 'light_cyan');
    }

    /**
     * work out the current one to run!
     *
     * @return string
     */
    protected function workOutMethodsToRun()
    {
        if ($this->runInteractively) {
            if ($this->startFrom || $this->endWith) {
                user_error('In interactive mode you can not set StartFrom / EndWith / OnlyRun.');
            }
            if ($this->onlyRun) {
            } else {
                $lastMethod = $this->getSessionValue('Completed');
                if ($lastMethod) {
                    $this->verbose = false;
                    $arrayKeys = array_keys($this->listOfTasks);
                    $found = false;
                    foreach ($arrayKeys as $index => $key) {
                        if ($key === $lastMethod) {
                            $found = true;
                            if ($this->runLastOneAgain) {
                                $this->onlyRun = $arrayKeys[$index];
                            } else {
                                if (isset($arrayKeys[$index + 1])) {
                                    if (isset($this->listOfTasks[$arrayKeys[$index + 1]])) {
                                        $this->onlyRun = $arrayKeys[$index + 1];
                                    } else {
                                        user_error('Can not find next task: ' . $arrayKeys[$index + 1]);
                                    }
                                } else {
                                    $this->deleteSession();
                                    die('
==========================================
Session has completed.
==========================================
                                    ');
                                }
                            }
                        }
                    }
                    if (! $found) {
                        user_error('Did not find next step.');
                    }
                } else {
                    $this->verbose = true;
                    reset($this->listOfTasks);
                    $this->onlyRun = key($this->listOfTasks);
                }
            }
        }
    }

    protected function nextStep()
    {
    }

    /**
     * start the method ...
     * - should we run it?
     *
     * @param  string $name whatever is listed in the listOfTasks
     * @return bool
     */
    protected function shouldWeRunIt($name): bool
    {
        $runMe = true;
        if ($this->onlyRun) {
            return $name === $this->onlyRun ? true : false;
        }
        if ($this->lastMethodHasBeenRun) {
            $runMe = false;
        } else {
            if ($this->startFrom) {
                $runMe = false;
                if ($name === $this->startFrom) {
                    $this->startFrom = '';
                }
            }
            if ($this->endWith) {
                if ($name === $this->endWith) {
                    $this->lastMethodHasBeenRun = true;
                }
            }
        }

        //here we call the PHP2CommandLine

        return $runMe;
    }

    protected function getSessionFileLocation()
    {
        return trim(
            $this->getAboveWebRootDirLocation() .
            '/' .
            $this->sessionFileName .
            '_' .
            $this->getVendorNamespace() .
            '_' .
            $this->getPackageNamespace() .
            '.json'
        );
    }

    protected function initSession()
    {
        if (! file_exists($this->getSessionFileLocation())) {
            $this->setSessionData(['Started' => date('Y-m-d h:i ')]);
        }
    }

    protected function deleteSession()
    {
        unlink($this->getSessionFileLocation());
    }

    protected function getSessionValue($key)
    {
        $session = $this->getSessionData();
        if (isset($session[$key])) {
            return $session[$key];
        }
        return null;
    }

    protected function getSessionData()
    {
        $this->initSession();
        $data = file_get_contents($this->getSessionFileLocation());
        if (! $data) {
            user_error('Could not read from: ' . $this->getSessionFileLocation());
        }
        return json_decode($data, true);
    }

    /**
     * @param array $session
     */
    protected function setSessionData($session)
    {
        $data = json_encode($session, JSON_PRETTY_PRINT);
        try {
            $file = fopen($this->getSessionFileLocation(), 'w');
            if ($file === false) {
                throw new \RuntimeException('Failed to open file: ' . $this->getSessionFileLocation());
            }
            $writeOutcome = fwrite($file, $data);
            if ($writeOutcome === false) {
                throw new \RuntimeException('Failed to write file: ' . $this->getSessionFileLocation());
            }
            $closeOutcome = fclose($file);
            if ($closeOutcome === false) {
                throw new \RuntimeException('Failed to close file: ' . $this->getSessionFileLocation());
            }
        } catch (\Exception $e) {
            // send error message if you can
            $this->colourPrint(
                'Caught exception: ' . $e->getMessage(),
                'red',
                2
            );
        }
    }

    protected function setSessionValue($key, $value)
    {
        $session = $this->getSessionData();
        $session[$key] = trim($value);
        $this->setSessionData($session);
    }

    protected function URLExists($url)
    {
        if ($url) {
            $headers = get_headers($url);
            if (is_array($headers) && count($headers)) {
                foreach ($headers as $header) {
                    if (substr($header, 9, 3) === '200') {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    protected function newLine()
    {
        if (PHP_SAPI === 'cli') {
            return PHP_EOL;
        }
        return nl2br("\n");
    }
}
