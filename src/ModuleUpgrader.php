<?php

namespace Sunnysideup\UpgradeToSilverstripe4;

use Sunnysideup\PHP2CommandLine\PHP2CommandLineSingleton;

use Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes\Ss31ToSs37;
use Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes\Ss33ToSs37;
use Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes\Ss35ToSs37;
use Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes\Ss3ToSs4;

class ModuleUpgrader extends ModuleUpgraderBaseWithVariables
{


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

    public function reinit()
    {
        $this->startPHP2CommandLine();

        return $this;
    }

    public function destroy()
    {
        self::$singleton = null;
    }

    /**
     * Create the only instance of me and return it
     * @return ModuleUpgrader
     */
    public static function create()
    {
        if (self::$singleton === null) {
            self::$singleton = new self();
        }
        return self::$singleton;
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
            user_error(
                'Could not find ' . $taskName . '.
                Choose from ' . implode(', ', array_keys($this->listOfTasks))
            );
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

    ###############################
    # USEFUL COMMANDS
    ###############################

    /**
     * Executes given operations on the PHP2CommandLineSingleton instance
     * Documentation for this can be found in the PHP2CommandLineSingleton module
     *
     * @param  string  $newDir                  root dir for ommand
     * @param  string  $command                 actual command
     * @param  string  $comment                 comment
     * @param  boolean $alwaysRun               run even if you are just preparing a real run. Default FALSE
     * @param  string  $keyNotesLogFileLocation
     *
     * @return array
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
        if (count($codeDirs) === 0) {
            user_error('
                Could not find any code dirs. The locations searched: ' . print_r($locations, 1)
                . ' Using the ' . $this->getIsModuleUpgradeNice() . ' approach');
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
     * @return string
     */
    public function checkIfPathExistsAndCleanItUp($path, $returnEvenIfItDoesNotExists = false)
    {
        $path = str_replace('///', '/', $path);
        $path = str_replace('//', '/', $path);
        if (file_exists($path)) {
            $path = realpath($path);
        }
        if (file_exists($path) || $returnEvenIfItDoesNotExists) {
            return rtrim($path, '/');
        }

        return '';
    }

    ###############################
    # RUN
    ###############################

    public function createListOfTasks()
    {
        foreach (array_keys($this->getAvailableRecipes()) as $recipeKey) {
            $html = '';
            $this->applyRecipe($recipeKey);
            $html .= '<h1>List of Tasks in run order for recipe: ' . $recipeKey . '</h1>';
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
                    $html .= '<h4>' . $count . '/' . $totalCount . ': ' . $obj->getTitle() . '</h4>';
                    $html .= '<p>' . $obj->getDescription() . '<br />';
                    $html .= '<strong>Code: </strong>' . $class;
                    $html .= '<br /><strong>Class Name: </strong>';
                    $html .= '<a href="' . $path . '">' . $reflectionClass->getShortName() . '</a>';
                    $html .= '</p>';
                    $obj = $properClass::deleteTask($params);
                } else {
                    user_error($properClass . ' could not be found as class', E_USER_ERROR);
                }
            }
            $dir = __DIR__ . '/../docs/en/';

            $html = str_replace(' _', ' \_', $html);

            file_put_contents(
                $dir . '/AvailableTasks.md',
                $html
            );
        }
    }

    /**
     * Starts the command line output and prints some opening information to the output
     * also initalises various environment variables
     */
    public function run()
    {
        $this->applyRecipe();
        for ($i = 0; $i < 5; $i++) {
            $this->colourPrint(
                str_repeat('_', 72),
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
        $this->loadGlobalVariables();
        foreach ($this->arrayOfModules as $moduleDetails) {
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
                    user_error(
                        $properClass . ' could not be found as class.
                        You can add namespacing to include your own classes.',
                        E_USER_ERROR
                    );
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
     * @return PHP2CommandLineSingleton
     */
    public function startPHP2CommandLine(): PHP2CommandLineSingleton
    {
        $this->commandLineExec = PHP2CommandLineSingleton::create();

        return $this->commandLineExec;
    }

    protected function applyRecipe($recipeName = null)
    {
        if ($recipeName === null) {
            $recipeName = $this->getRecipe();
        }
        if ($recipeName) {
            if (isset($this->availableRecipes[$recipeName])) {
                $recipeClass = $this->availableRecipes[$recipeName];
                $obj = new $recipeClass();
                $vars = $obj->getVariables();
                foreach ($vars as $variable => $value) {
                    $method = 'set' . ucwords($variable);
                    $this->{$method}($value);
                }
            } else {
                user_error(
                    'Recipe ' . $recipeName . ' not available.
                    Available Recipes are: ' . print_r($this->getAvailableRecipes())
                );
            }
        }
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
        $this->restartSession = $this->getCommandLineOrArgumentAsBoolean('restart');
        $this->runLastOneAgain = $this->getCommandLineOrArgumentAsBoolean('again');
        //todo next / previous / etc...
    }

    protected function loadGlobalVariables()
    {
        $this->aboveWebRootDirLocation = $this->checkIfPathExistsAndCleanItUp($this->aboveWebRootDirLocation);
        $this->webRootDirLocation = $this->checkIfPathExistsAndCleanItUp(
            $this->aboveWebRootDirLocation . '/' . $this->webRootName,
            true
        );
        $this->themeDirLocation = $this->checkIfPathExistsAndCleanItUp($this->webRootDirLocation . '/themes', true);
    }

    protected function getCommandLineOrArgumentAsBoolean(string $variableName = '') : bool
    {
        if (PHP_SAPI === 'cli') {
            return isset($this->argv[1]) && $this->argv[1] === $variableName ? true : false;
        } else {
            return isset($_GET[$variableName]) ? true : false;
        }
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
        $this->gitLinkAsHTTPS = str_replace(
            'git@github.com:',
            'https://github.com/',
            $gitLinkWithoutExtension
        );
        $this->gitLinkAsRawHTTPS = str_replace(
            'git@github.com:',
            'https://raw.githubusercontent.com/',
            $gitLinkWithoutExtension
        );

        //Origin Composer FileLocation
        $this->originComposerFileLocation = $moduleDetails['OriginComposerFileLocation'] ?? '';

        $this->workoutPackageFolderName($moduleDetails);

        //moduleDirLocation
        if ($this->isModuleUpgrade) {
            $this->moduleDirLocations = [
                $this->webRootDirLocation . '/' . $this->packageFolderNameForInstall,
            ];
            $this->themeDirLocation = '';
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
            $this->logFileLocation =
                $this->logFolderDirLocation . '/' . $this->packageName .
                '-upgrade-log.' . time() .
                '.txt';
            $this->commandLineExec->setLogFileLocation($this->logFileLocation);
        } else {
            $this->commandLineExec->setLogFileLocation('');
        }

        if ($this->restartSession) {
            $this->getSessionManager()->deleteSession();
        }
    }

    protected function workoutPackageFolderName(array $moduleDetails)
    {
        $this->packageFolderNameForInstall = trim($this->packageFolderNameForInstall);
        if ($this->packageFolderNameForInstall) {
            //do nothing
        } else {
            if ($this->getSessionValue('PackageFolderNameForInstall')) {
                $this->packageFolderNameForInstall = $this->getSessionValue('PackageFolderNameForInstall');
            } else {
                if (isset($moduleDetails['PackageFolderNameForInstall'])) {
                    $this->packageFolderNameForInstall = $moduleDetails['PackageFolderNameForInstall'];
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
                            $this->packageFolderNameForInstall = $this->workoutPackageFolderNameBasic();
                        }
                    } else {
                        $this->packageFolderNameForInstall = $this->workoutPackageFolderNameBasic();
                    }
                }
            }
            $this->setSessionValue('PackageFolderNameForInstall', $this->packageFolderNameForInstall);
        }
    }

    protected function workoutPackageFolderNameBasic()
    {
        if ($this->isModuleUpgrade) {
            return $this->packageName;
        }
        return 'mysite';
    }

    protected function printVarsForModule()
    {
        //output the confirmation.
        $this->colourPrint('---------------------', 'light_cyan');
        $this->colourPrint('UPGRADE DETAILS', 'light_cyan');
        $this->colourPrint('---------------------', 'light_cyan');
        $this->colourPrint('- Type: ' . $this->getIsModuleUpgradeNice(), 'light_cyan');
        $this->colourPrint('- Recipe: ' . ($this->getRecipe() ?: 'no recipe selected'), 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Vendor Name: ' . $this->vendorName, 'light_cyan');
        $this->colourPrint('- Package Name: ' . $this->packageName, 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Upgrade as Fork: ' . ($this->upgradeAsFork ? 'yes' : 'no'), 'light_cyan');
        $this->colourPrint('- Run Interactively: ' . ($this->runInteractively ? 'yes' : 'no'), 'light_cyan');
        $this->colourPrint('- Run Irreversibly: ' . ($this->runIrreversibly ? 'yes' : 'no'), 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Vendor Namespace: ' . $this->vendorNamespace, 'light_cyan');
        $this->colourPrint('- Package Namespace: ' . $this->packageNamespace, 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Upgrade Dir (root of install): ' . $this->getWebRootDirLocation(), 'light_cyan');
        $this->colourPrint('- Package Folder Name For Install: ' . $this->packageFolderNameForInstall, 'light_cyan');
        $this->colourPrint('- Module / Project Dir(s): ' . implode(', ', $this->moduleDirLocations), 'light_cyan');
        $this->colourPrint('- Theme Dir: ' . ($this->themeDirLocation ?: 'not set'), 'light_cyan');
        $this->colourPrint('- Git and Composer Root Dir: ' . $this->getGitRootDir(), 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Git Repository Link (SSH): ' . $this->gitLink, 'light_cyan');
        $this->colourPrint('- Git Repository Link (HTTPS): ' . $this->gitLinkAsHTTPS, 'light_cyan');
        $this->colourPrint('- Git Repository Link (RAW): ' . $this->gitLinkAsRawHTTPS, 'light_cyan');
        $this->colourPrint('- Origin composer file location: ' .
            ($this->originComposerFileLocation ?: 'not set'), 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Session file: ' . $this->getSessionFileLocation(), 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Last Step: ' . ($this->getSessionValue('Completed') ?: 'not set'), 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Log File Location: ' . ($this->logFileLocation ?: 'not logged'), 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- List of Steps: ' . $this->newLine() . '    -' .
            implode($this->newLine() . '    -', array_keys($this->listOfTasks)), 'light_cyan');
        $this->colourPrint('---------------------', 'light_cyan');
        $this->colourPrint('- parameter "again" ... runs last comand again', 'light_cyan');
        $this->colourPrint('- parameter "restart" ... starts process from beginning', 'light_cyan');
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


    protected function URLExists($url): bool
    {
        if ($url && $this->isValidURL($url)) {
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

    protected function isValidURL($url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        return true;
    }

    protected function newLine()
    {
        if (PHP_SAPI === 'cli') {
            return PHP_EOL;
        }
        return nl2br("\n");
    }

    protected function getIsModuleUpgradeNice()
    {
        return $this->getIsModuleUpgrade() ? 'module upgrade' : 'website project upgrade';
    }
}
