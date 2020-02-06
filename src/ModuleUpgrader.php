<?php

namespace Sunnysideup\UpgradeToSilverstripe4;

use Sunnysideup\PHP2CommandLine\PHP2CommandLineSingleton;

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
    public function colourPrint($mixedVar, string $colour = 'dark_gray', $newLineCount = 1)
    {
        return $this->commandLineExec->colourPrint($mixedVar, $colour, $newLineCount);
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
        // $originalPath = $path;
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
                            $this->getSessionManager()->setSessionValue('Completed', $class);
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

    public function applyRecipe($recipeName = null)
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
    }

    protected function loadGlobalVariables()
    {
        $this->aboveWebRootDirLocation = $this->checkIfPathExistsAndCleanItUp(
            $this->aboveWebRootDirLocation
        );
        $this->webRootDirLocation = $this->checkIfPathExistsAndCleanItUp(
            $this->aboveWebRootDirLocation . '/' . $this->webRootName,
            true
        );
        $this->themeDirLocation = $this->checkIfPathExistsAndCleanItUp(
            $this->webRootDirLocation . '/themes',
            true
        );
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
            $this->vendorNamespace = $this->cleanCamelCase($this->vendorName);
        }

        //PackageName
        $this->packageName = $moduleDetails['PackageName'];

        //PackageNamespace
        if (isset($moduleDetails['PackageNamespace'])) {
            $this->packageNamespace = $moduleDetails['PackageNamespace'];
        } else {
            $this->packageNamespace = $this->cleanCamelCase($this->packageName);
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
            $packageFolderNameForInstall = $this->getSessionManager()->getSessionValue(
                'PackageFolderNameForInstall'
            );
            if ($packageFolderNameForInstall) {
                $this->packageFolderNameForInstall = $packageFolderNameForInstall;
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
                            $this->packageFolderNameForInstall = $this->getPackageFolderNameBasic();
                        }
                    } else {
                        $this->packageFolderNameForInstall = $this->getPackageFolderNameBasic();
                    }
                }
            }
            $this->getSessionManager()->setSessionValue(
                'PackageFolderNameForInstall',
                $this->packageFolderNameForInstall
            );
        }
    }

    protected function printVarsForModule($moduleDetails)
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
        $this->colourPrint('- Session file: ' . $this->getSessionManager()->getSessionFileLocation(), 'light_cyan');
        $this->colourPrint('- ---', 'light_cyan');
        $this->colourPrint('- Last Step: ' .
            ($this->getSessionManager()->getSessionValue('Completed') ?: 'not set'), 'light_cyan');
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
                $lastMethod = $this->getSessionManager()->getSessionValue('Completed');
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
                                    $this->getSessionManager()->deleteSession();
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

    protected function newLine()
    {
        if (PHP_SAPI === 'cli') {
            return PHP_EOL;
        }
        return nl2br("\n");
    }
}
