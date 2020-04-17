<?php

namespace Sunnysideup\UpgradeToSilverstripe4;

use Sunnysideup\PHP2CommandLine\PHP2CommandLineSingleton;

use Sunnysideup\UpgradeToSilverstripe4\Api\SessionManagement;
use Sunnysideup\UpgradeToSilverstripe4\Interfaces\ModuleUpgraderInterface;
use Sunnysideup\UpgradeToSilverstripe4\Interfaces\SessionManagementInterface;
use Sunnysideup\UpgradeToSilverstripe4\Traits\GettersAndSetters;

use Sunnysideup\UpgradeToSilverstripe4\Traits\Misc;
use Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes\Ss31ToSs37;
use Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes\Ss33ToSs37;
use Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes\Ss35ToSs37;

use Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes\Ss3ToSs4;

class ModuleUpgraderBaseWithVariables implements ModuleUpgraderInterface
{
    use GettersAndSetters;
    use Misc;

    #########################################
    # Arguments
    #########################################

    protected $argv = [];

    #########################################
    # RECIPE
    #########################################

    /**
     * @var string
     */
    protected $recipe = 'SS4';

    /**
     * list of recipes available
     * @var array
     */
    protected $availableRecipes = [
        'SS4' => Ss3ToSs4::class,
        'SS31-37' => Ss31ToSs37::class,
        'SS33-37' => Ss33ToSs37::class,
        'SS35-37' => Ss35ToSs37::class,
    ];

    #########################################
    # TASKS
    #########################################

    /**
     * A list of task groups
     * this will be set from recipe - so we need this!
     * @var array
     */
    protected $taskSteps = [];

    /**
     * An array of all the 'taskName's of the tasks that you wish to run during the execution of this upgrader task.
     * This array can be overriden in the example-index.php file that you create.
     * You can enter a full name space if you need to.
     * The final -x will be removed.  We add -1 or -2 to run the same task multiple times.
     *
     * @var array
     */
    protected $listOfTasks = [];

    /**
     * e.g. ^4.4
     * @var string
     */
    protected $frameworkComposerRestraint = '';

    /**
     * Should the session details be deleted before we start?
     * @var bool
     */
    protected $restartSession = false;

    /**
     * Do we run the last step again?
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
    protected $runInteractively = true;

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
     * @var bool
     */
    protected $runIrreversibly = false;

    /**
     * is this out of order - i.e. no influence on next task
     * @var bool
     */
    protected $outOfOrderTask = false;

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
     * name of the branch that exists as the starting point for upgrade
     * @var string branch name
     */
    protected $nameOfBranchForBaseCode = 'master';

    /**
     * name of the branch to be created that we use a starter branch for upgrade
     * @var string branch name
     */
    protected $nameOfUpgradeStarterBranch = 'upgrades/starting-point';

    /**
     * name of the branch created to do the upgrade
     * @var string branch name
     */
    protected $nameOfTempBranch = 'upgrades/temp-automated-upgrade-branch';

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
     * @var PHP2CommandLineSingleton|null
     */
    protected $commandLineExec = null;

    /**
     *Reference to the commandline printer that outputs everything to the command line
     * @var SessionManagementInterface|null
     */
    protected $sessionManager = null;

    /**
     * @var string
     */
    protected $sessionFileName = 'Session_For';

    /**
     * does the exec output Key Notes?
     * @var bool
     */
    protected $makeKeyNotes = false;

    /**
     * @var string
     */
    protected $originComposerFileLocation = '';

    /**
     * Is this the last TASK we are running?
     * @var bool
     */
    protected $lastMethodHasBeenRun = false;

    /**
     * @var string
     */
    protected $logFileLocation = '';

    /**
     * Combination of the web dir root name and the aboveWebRootDirLocation
     * @var string
     */
    protected $webRootDirLocation = '';

    /**
     * Combination of the web dir root name and the aboveWebRootDirLocation
     * @var string
     */
    protected $themeDirLocation = '';

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
    protected $moduleDirLocations = [];

    /**
     * Starts the output to the commandline / browser
     */
    public function __construct()
    {
        global $argv;
        $this->argv = $argv;
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
     * Appends the given module in the form of all its module data that has to be formatted in an array
     * to the array of modules that will be worked with during the upgrade procedure.
     *
     * @param array $a data to append
     * @return ModuleUpgraderInterface
     */
    public function addModule(array $a): ModuleUpgraderInterface
    {
        $this->arrayOfModules[] = $a;

        return $this;
    }

    /**
     * Inserts another task to the list of tasks at a given position in the order of execution, if it is set
     * TODO These parameter names need some more refining
     * @param string|array  $oneOrMoreTasks the tasks to be inserted
     * @param bool          $insertBeforeOrAfter If to insert before or after
     * @param bool          $isBefore
     *
     * @return ModuleUpgraderInterface
     */
    public function addToListOfTasks($oneOrMoreTasks, $insertBeforeOrAfter, $isBefore): ModuleUpgraderInterface
    {
        if (! is_array($oneOrMoreTasks)) {
            $oneOrMoreTasks = [$oneOrMoreTasks];
        }
        foreach ($this->listOfTasks as $key => $task) {
            if ($task === $insertBeforeOrAfter) {
                if ($isBefore) {
                    $pos = $key - 1;
                } else {
                    $pos = $key;
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
     * Removes the given task from the list of tasks to execute
     * @param  string $s name of the task to remove
     *
     * @return ModuleUpgraderInterface
     */
    public function removeFromListOfTasks($s): ModuleUpgraderInterface
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
     * @param bool $b
     * @return ModuleUpgraderInterface
     */
    public function setRunImmediately(bool $b): ModuleUpgraderInterface
    {
        $this->commandLineExec->setRunImmediately($b);

        return $this;
    }

    /**
     * @param bool $b
     */
    public function setBreakOnAllErrors(bool $b)
    {
        $this->commandLineExec->setBreakOnAllErrors($b);

        return $this;
    }

    public function getRecipe(): string
    {
        return $this->recipe;
    }

    public function getAvailableRecipes(): array
    {
        return $this->availableRecipes;
    }

    public function getListOfTasks(): array
    {
        return $this->listOfTasks;
    }

    public function getIsModuleUpgrade(): bool
    {
        return $this->isModuleUpgrade;
    }

    public function getDefaultNamespaceForTasks(): string
    {
        return $this->defaultNamespaceForTasks;
    }

    public function getVendorNamespace(): string
    {
        return $this->vendorNamespace;
    }

    public function getPackageNamespace(): string
    {
        return $this->packageNamespace;
    }

    public function getAboveWebRootDirLocation()
    {
        return $this->aboveWebRootDirLocation;
    }

    public function getWebRootDirLocation(): string
    {
        return $this->webRootDirLocation;
    }

    /**
     * @return string
     */
    public function getLocationOfThisUpgrader(): string
    {
        if (! $this->locationOfThisUpgrader) {
            $this->locationOfThisUpgrader = dirname(__DIR__);
        }
        return $this->locationOfThisUpgrader;
    }

    /**
     * @return string [description]
     */
    public function getLocationOfSSUpgradeModule(): string
    {
        if (! $this->locationOfSSUpgradeModule) {
            $this->locationOfSSUpgradeModule = $this->getLocationOfThisUpgrader() .
                '/vendor/silverstripe/upgrader/bin/upgrade-code';
        }
        return $this->locationOfSSUpgradeModule;
    }

    public function getSessionManager(): SessionManagementInterface
    {
        if ($this->sessionManager === null) {
            $sessionFileLocation = trim(
                $this->getAboveWebRootDirLocation() .
                '/' .
                $this->sessionFileName .
                '_' .
                $this->getVendorNamespace() .
                '_' .
                $this->getPackageNamespace() .
                '.json'
            );
            $this->sessionManager = SessionManagement::initSession($sessionFileLocation);
        }

        return $this->sessionManager;
    }

    /**
     * returns an array of existing paths
     *
     * @return array
     */
    public function getExistingModuleDirLocations(): array
    {
        $array = [];
        foreach ($this->moduleDirLocations as $location) {
            $location = (string) $this->checkIfPathExistsAndCleanItUp($location, false);
            if ($location) {
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

    /**
     * Whether execution should come to a halt when an error is reached
     * @return bool
     */
    public function getBreakOnAllErrors(): bool
    {
        return $this->commandLineExec->getBreakOnAllErrors();
    }

    /**
     * Whether execution should come to a halt when an error is reached
     * @return bool
     */
    public function getIsProjectUpgrade(): bool
    {
        return $this->isModuleUpgrade ? false : true;
    }

    public function getExistingModuleDirLocationsWithThemeFolders(): array
    {
        $array = $this->getExistingModuleDirLocations();
        if ($this->themeDirLocation) {
            $array[$this->themeDirLocation] = $this->themeDirLocation;
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
        return array_shift($locations);
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
        if (count($codeDirs) === 0) {
            user_error('
                Could not find any code dirs. The locations searched: ' . print_r($locations, true)
                . ' Using the ' . $this->getIsModuleUpgradeNice() . ' approach');
        }

        return $codeDirs;
    }

    public function findMyCodeDir($moduleDir): string
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

        //return empty string
        return '';
    }

    public function getGitRootDir(): string
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

    public function getIsModuleUpgradeNice(): string
    {
        return $this->getIsModuleUpgrade() ? 'module upgrade' : 'website project upgrade';
    }

    /**
     * Removes the given task from the list of tasks to execute
     * @param  string $taskName name of the task
     * @param  string $variableName name of the task
     * @param  mixed $variableValue name of the task
     *
     * @return  ModuleUpgraderInterface
     */
    protected function setVariableForTask($taskName, $variableName, $variableValue): ModuleUpgraderInterface
    {
        $key = $this->positionForTask($taskName);
        if ($key !== false) {
            $this->listOfTasks[$taskName][$variableName] = $variableValue;
        } else {
            user_error(
                'Could not find ' . $taskName . '.
                Choose from ' . implode(', ', array_keys($this->listOfTasks))
            );
        }

        return $this;
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

    protected function getPackageFolderNameBasic(): string
    {
        if ($this->isModuleUpgrade) {
            return $this->packageName;
        }
        return 'mysite';
    }

    /**
     * Starts the logger. Extra checking may be put in here to see if you
     * want to start the logger or not in different scenarios.
     *
     * For now it defaults to always existing
     * @return PHP2CommandLineSingleton
     */
    protected function startPHP2CommandLine(): PHP2CommandLineSingleton
    {
        $this->commandLineExec = PHP2CommandLineSingleton::create();

        return $this->commandLineExec;
    }

    /**
     * deconstructs Command Line
     * important as this outputs the whole thing
     */
    protected function endPHP2CommandLine()
    {
        if ($this->commandLineExec !== null) {
            PHP2CommandLineSingleton::delete();
            $this->commandLineExec = null;
        }
    }
}
