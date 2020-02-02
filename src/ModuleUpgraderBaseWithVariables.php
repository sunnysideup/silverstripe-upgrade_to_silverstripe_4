<?php

namespace Sunnysideup\UpgradeToSilverstripe4;

use Sunnysideup\PHP2CommandLine\PHP2CommandLineSingleton;

use Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes\Ss31ToSs37;
use Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes\Ss33ToSs37;
use Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes\Ss35ToSs37;
use Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes\Ss3ToSs4;

class ModuleUpgraderBaseWithVariables
{
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
     *
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
     * @return string
     */
    public function getLocationOfThisUpgrader() : string
    {
        if (! $this->locationOfThisUpgrader) {
            $this->locationOfThisUpgrader = dirname(__DIR__);
        }
        return $this->locationOfThisUpgrader;
    }

    /**
     * //e.g. 'upgrade-code'
     * //e.g. '~/.composer/vendor/bin/upgrade-code'
     * //e.g. '/var/www/silverstripe-upgrade_to_silverstripe_4/vendor/silverstripe/upgrader/bin/upgrade-code'
     * @var string
     */
    protected $locationOfSSUpgradeModule = '';

    /**
     * @return string [description]
     */
    public function getLocationOfSSUpgradeModule() : string
    {
        if (! $this->locationOfSSUpgradeModule) {
            $this->locationOfSSUpgradeModule = $this->getLocationOfThisUpgrader() .
                '/vendor/silverstripe/upgrader/bin/upgrade-code';
        }
        return $this->locationOfSSUpgradeModule;
    }

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
     * @var PHP2CommandLineSingleton|null
     */
    protected $sessionManager = null;

    public function getSessionManager()
    {
        if ($this->sessionManager === null) {
            $this->sessionManager = new SessionManagement;
        }

        return $this->sessionManager;
    }

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
     * Holds the only instance of me
     * @var ModuleUpgrader|null
     */
    private static $singleton = null;

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




}
