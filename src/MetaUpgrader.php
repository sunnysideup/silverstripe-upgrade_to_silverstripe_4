<?php

namespace Sunnysideup\UpgradeToSilverstripe4;
use Sunnysideup\UpgradeToSilverstripe4\Util\PHP2CommandLine;
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
        if (! self::$_singleton) {
            self::$_singleton = new MetaUpgrader();
        }
        return self::$_singleton;
    }

    /**
     * set a folder location for the upgrade log.
     * if set, a log will be created.
     *
     * @var string
     */
    protected $logFolderLocation = '';

    public function setLogFolderLocation($s)
    {
        $this->logFolderLocation = $s;

        return $this;
    }

    /**
     * The folder for storing the log file in.
     * @param [type] $s [description]
     */
    public function getLogFolderLocation()
    {
        return $this->logFolderLocation;
    }

    /**
     * The file location for storing the update logs.
     * @return [type] [description]
     */
    public function getLogFileLocation(){
        return $this->logFileLocation;
    }

    /**
     * start the upgrade sequence at a particular method
     * @var string
     */
    protected $listOfTasks = [
        'ResetWebRootDir' = [],
        'AddUpgradeBranch' = [],
        'UpdateComposerRequirements_1' => [
            'Package' => 'silverstripe/framework',
            'NewVersion' => '~4.0'
        ],
        'UpdateComposerRequirements_1' => [
            'Package' => 'silverstripe/cms',
            'ReplacementPackage' => 'silverstripe/recipe-cms',
            'NewVersion' => '1.1.0'
        ],
        'Recompose' => [],
        // 'ResetWebRootDir' => [],
        'ComposerInstallProject' => [],
        // 'ChangeEnvironment' => [],
        'UpperCaseFolderNamesForPSR4' => [],
        'AddHacks' => [],
        'AddNamespace' => [],
        'Upgrade' => [],
        'InspectAPIChanges' => [],
        'Reorganise' => [],
        // 'WebRootUpdate' => []
    ];

    public function setListOfTasks($a)
    {
        $this->listOfTasks = $a;

        return $this;
    }

    public function removeFromListOfTasks($s)
    {
        if ($key = $this->positionForTask($s) !== false) {
            unset($messages[$key]);
        }

        return $this;
    }

    public function addToListOfTasks($oneOrMoreTasks, $insertBeforeOrAfter, $isBefore)
    {
        if(! is_array($oneOrMoreTasks)) {
            $oneOrMoreTasks = [$oneOrMoreTasks]
        }
        foreach($this->listOfTasks as $key => $task) {
            if($task === $insertBeforeOrAfter) {
                if($isBefore) {
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
     * start the upgrade sequence at a particular method
     * @var string
     */
    protected $startFrom = '';

    public function setStartFrom($s)
    {
        $this->startFrom = $s;

        return $this;
    }

    /**
     * end the upgrade sequence after a particular method
     * @var string
     */
    protected $endWith = '';

    public function setEndWith($s)
    {
        $this->endWith = $s;

        return $this;
    }

    /**
     * end the upgrade sequence after a particular method
     * @var string
     */
    protected $defaultNameSpaceForTasks = 'Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks';

    public function setDefaultNameSpaceForTasks($s)
    {
        $this->defaultNameSpaceForTasks = $s;

        return $this;
    }

    /**
     * should the script stop if any error occurs?
     * @var bool
     */
    protected $breakOnAllErrors = false;

    public function setBreakOnAllErrors($b)
    {
        $this->breakOnAllErrors = $b;

        return $this;
    }

    /**
     * name of the branch created to do the upgrade
     * @var string
     */
    protected $nameOfTempBranch = 'temp-upgradeto4-branch';

    public function setNameOfTempBranch($s)
    {
        $this->nameOfTempBranch = $s;

        return $this;
    }


    /**
     * @var string
     */
    protected $aboveWebRootDir = '/var/www';

    public function setAboveWebRootDir($s)
    {
        $this->aboveWebRootDir = $s;

        return $this;
    }

    /**
     * @var string
     */
    protected $webrootDirName = 'upgradeto4';

    public function setWebRootDirName($s)
    {
        $this->webrootDirName = $s;

        return $this;
    }

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
     *          'PackageNameSpace' => 'Package2',
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

    public function setArrayOfModules($a)
    {
        $this->arrayOfModules = $a;

        return $this;
    }

    public function addModule($a)
    {
        $this->arrayOfModules[] = $a;

        return $this;
    }

    /**
     * @var null|bool
     */
    protected $runImmediately = null;

    public function setRunImmediately($b)
    {
        $this->runImmediately = $b;

        return $this;
    }

    /**
     *
     * e.g. COMPOSER_HOME="/home/UserName"
     *
     * @var string
     */
    protected $composerEnvironmentVars = '';

    public function setComposerEnvironmentVars($s)
    {
        $this->composerEnvironmentVars = $s;

        return $this;
    }



    /**
     * //e.g. 'upgrade-code'
     * //e.g. '~/.composer/vendor/bin/upgrade-code'
     * //e.g. '/var/www/silverstripe-upgrade_to_silverstripe_4/vendor/silverstripe/upgrader/bin/upgrade-code'
     * @var string
     */
    protected $locationOfUpgradeModule = 'upgrade-code';

    public function setLocationOfUpgradeModule($s)
    {
        $this->locationOfUpgradeModule = $s;

        return $this;
    }

    /**
     * @var bool
     */
    protected $includeEnvironmentFileUpdate = false;

    public function setIncludeEnvironmentFileUpdate($b)
    {
        $this->includeEnvironmentFileUpdate = $b;

        return $this;
    }

    /**
     * @var bool
     */
    protected $includeReorganiseTask = false;

    public function setIncludeReorganiseTask($b)
    {
        $this->includeReorganiseTask = $b;

        return $this;
    }

    /**
     * @var bool
     */
    protected $includeWebRootUpdateTask = false;

    public function setIncludeWebRootUpdateTask($b)
    {
        $this->includeWebRootUpdateTask = $b;

        return $this;
    }

    protected $lastMethod = false;


    protected $logFileLocation = '';

    public function getLogFileLocation()
    {
        return $this->logFileLocation;
    }


    protected $webrootDir = '';

    public function getWebRootDir()
    {
        return $this->webrootDir;
    }

    protected $moduleDir = '';

    public function getModuleDir()
    {
        return $this->moduleDir;
    }

    protected $vendorName = '';

    public function getVendorName()
    {
        return $this->vendorName;
    }


    protected $vendorNamespace = '';

    public function getVendorNamespace()
    {
        return $this->vendorNamespace;
    }


    protected $packageName = '';

    public function getPackageName()
    {
        return $this->PackageName;
    }


    protected $packageNamespace = '';

    public function getPackageNamespace()
    {
        return $this->packageNamespace;
    }


    protected $gitLink = '';

    public function getGitLink()
    {
        return $this->getGitLink;
    }


    protected $upgradeAsFork = '';

    public function getUpgradeAsFork()
    {
        return $this->upgradeAsFork;
    }


    /**
     *
     * @var Sunnysideup\UpgradeToSilverstripe4\Util\PHP2CommandLine|null
     */
    protected $commandLineExec = null;

    public function run()
    {
        //Init UTIL and helper objects
        $this->colourPrint(
            '===================== START ======================',
            'light_red',
            5
        );

        $this->startPHP2CommandLine();
        $this->startOutput();

        if ($this->runImmediately !== null) {
            $this->commandLineExec->setRunImmediately($this->runImmediately);
        }
        $this->aboveWebRootDir = $this->checkIfPathExistsAndCleanItUp($this->aboveWebRootDir);
        $this->webrootDir = $this->checkIfPathExistsAndCleanItUp($this->aboveWebRootDir.'/'.$this->webrootDirName);
        foreach ($this->arrayOfModules as $counter => $moduleDetails) {

            $this->loadVarsForModule($moduleDetails);

            foreach($this->listOfActions as $class => $params) {

                $baseClass = $class;
                if(! class_exists($class)) {
                    $class = $this->defaultNameSpaceForTasks.'\\'.$class;
                }
                if(class_exists($class)) {
                    if ($this->startMethod($class)) {
                        $obj = $class::create($this, $params);
                        $obj->run();
                    }
                } else {
                    user_error($baseClass.' or '.$class.' could not be found as class', E_USER_ERROR);
                }
            }

        }
        $this->colourPrint(
            '===================== END =======================',
            'light_red',
            5
        );
        $this->endOutput();
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
        $this->commandLineExec = PHP2CommandLine::create($this->logFileLocation);
    }

    /**
     * deconstructs Command Line
     * important as this outputs the whole thing
     */
    protected function endPHP2CommandLine()
    {
        $this->commandLineExec = PHP2CommandLine::delete();
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
        $this->moduleDir = $this->webrootDir . '/' . $this->packageName;
        if (isset($moduleDetails['GitLink'])) {
            $this->gitLink = $moduleDetails['GitLink'];
        } else {
            $this->gitLink = 'git@github.com:'.$this->vendorName.'/silverstripe-'.$this->packageName;
        }

        //UpgradeAsFork
        $this->upgradeAsFork = empty($moduleDetails['UpgradeAsFork']) ? false : true;

        //LogFileLocation
        $this->logFileLocation = '';
        if ($this->logFolderLocation) {
            $this->logFileLocation = $this->logFolderLocation.'/'.$this->packageName.'-upgrade-log.'.time().'.txt';
        }
        $this->$commandLineExec->setLogFileLocation($this->logFileLocation);


        //output the confirmation.
        $this->$colourPrinter->colourPrint('---------------------', 'light_cyan');
        $this->$colourPrinter->colourPrint('UPGRADE DETAILS', 'light_cyan');
        $this->$colourPrinter->colourPrint('---------------------', 'light_cyan');
        $this->$colourPrinter->colourPrint('Vendor Name: '.$this->vendorName, 'light_cyan');
        $this->$colourPrinter->colourPrint('Vendor Namespace: '.$this->vendorNamespace, 'light_cyan');
        $this->$colourPrinter->colourPrint('Package Name: '.$this->packageName, 'light_cyan');
        $this->$colourPrinter->colourPrint('Package Namespace: '.$this->packageNamespace, 'light_cyan');
        $this->$colourPrinter->colourPrint('Module Dir: '.$this->moduleDir, 'light_cyan');
        $this->$colourPrinter->colourPrint('Git Repository Link: '.$this->gitLink, 'light_cyan');
        $this->$colourPrinter->colourPrint('Upgrade as Fork: '.($this->upgradeAsFork ? 'yes' : 'no'), 'light_cyan');
        $this->$colourPrinter->colourPrint('Log File Location: '.($this->logFileLocation ? $this->logFileLocation : 'not logged'), 'light_cyan');
        $this->$colourPrinter->colourPrint('---------------------', 'light_cyan');
    }



    ##############################################################################
    ##############################################################################
    ##############################################################################


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
        if ($this->lastMethod) {
            $runMe = false;
        } else {
            if ($this->startFrom) {
                if ($name === $this->startFrom) {
                    $this->startFrom = '';
                }
            }
            if ($this->endWith) {
                if ($name === $this->endWith) {
                    $this->lastMethod = true;
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

    protected function execMe($newDir, $command, $comment, $alwaysRun = false)
    {
        return $this->commandLineExec->execMe($newDir, $command, $comment, $alwaysRun);
    }

    protected function colourPrint($mixedVar, $colour, $newLineCount)
    {
        return $this->commandLineExec->colourPrint($mixedVar, $colour, $newLineCount);
    }

    protected function positionForTask($s)
    {
        return array_search($s, $this->listOfTasks);
    }



    protected function findCodeDir()
    {
        $codeDir = '';
        if (file_exists($this->moduleDir . '/code')) {
            $codeDir = $this->moduleDir . '/code';
        } elseif (file_exists($this->moduleDir . '/src')) {
            $codeDir = $this->moduleDir . '/src';
        } else {
            user_error('Can not find code dir for '.$this->moduleDir, E_USER_NOTICE);
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


}
