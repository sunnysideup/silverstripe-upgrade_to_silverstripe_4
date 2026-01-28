<?php

namespace Sunnysideup\UpgradeSilverstripe\UpgradeRecipes;

class Ss4ToSs5 extends BaseClass
{
    /**
     * name of the branch to be created that we use a starter branch for upgrade
     * @var string branch name
     */
    protected $nameOfUpgradeStarterBranch = 'upgrades/starting-point/ss5-upgrade';

    /**
     * name of the branch created to do the upgrade
     * @var string branch name
     */
    protected $nameOfTempBranch = 'upgrades/automatedbranch/4to5';

    /**
     * The default namespace for all tasks
     * @var string
     */
    protected $defaultNamespaceForTasks = 'Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks';

    #########################################
    # TASKS
    #########################################

    /**
     * An array of all the 'taskNames of the tasks that you wish to run during the execution of this upgrader task.
     * This array can be overriden in the example-index.php file that you create.
     * You can enter a full name space if you need to.
     * The final -x will be removed.  We add -1 or -2 to run the same task multiple times.
     *
     * @var array
     */
    protected $listOfTasks = [
        //Step1: Prepare
        'WebRootDirCheckFoldersReady' => [],
        'WebRootDirReset-1' => [],
        'SwitchPhpVersion-1' => [
            'version' => '8.3',
        ],
        'CheckoutDefaultBranch-1' => [
            'branchOrTagToUse' => 'develop',
        ],

        'BranchAddUpgradeStarterBranch' => [],
        'CheckoutUpgradeStarterBranch-1' => [],
        'ComposerMakeRequirementsMoreLenient' => [],
        'LintPHPCompatabilityCheck' => [
            'phpVersion' => '8.3',
        ],
        'WebRootDirReset-2' => [],

        'CheckoutUpgradeStarterBranch-2' => [],
        'BranchesAddTempUpgradeBranch' => [],
        'ComposerBackupOriginalRequirements' => [],
        // 'LintOutdatedPHPStyles' => [],

        // 'AddTableNamePrivateStatic' => [],
        'ComposerRemoveRequirements' => [], // remove framework, etc...
        'ComposerUpdateRequirements' => [], // run find and replace for specific packages.

        'WebRootDirReset-3' => [],

        //Step2: MoveToNewVersion
        'ComposerInstallProject' => [],
        'ComposerAddOneByOne' => [],
        'ComposerCompatibilityCheckerStep3' => [],

        //Step3: FixBeforeStart
        'SearchAndReplace' => [
            'sourceFolders' => 'SS5',
        ],

        //step7: Lock-in
        'FinaliseUpgradeWithMergeIntoDefaultBranch' => [
            'branchToMergeInto' => 'develop',
        ],
    ];

    protected $frameworkComposerRestraint = '~5@stable';
}
