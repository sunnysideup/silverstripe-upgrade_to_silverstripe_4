<?php

namespace Sunnysideup\UpgradeSilverstripe\UpgradeRecipes;

class Ss5ToSs6ForModules extends BaseClass
{
    /**
     * name of the branch to be created that we use a starter branch for upgrade
     * @var string branch name
     */
    protected $nameOfUpgradeStarterBranch = 'upgrades/starting-point/5to6';

    /**
     * name of the branch created to do the upgrade
     * @var string branch name
     */
    protected $nameOfTempBranch = 'upgrades/automatedbranch/5to6';

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
        'CheckThatFoldersAreReady' => [],
        'ResetWebRootDir-1' => [],
        'SwitchPhpVersion-1' => [
            'version' => '8.3',
            'alsoReinstallWithComposer' => false,
        ],
        'CheckoutDefaultBranch-1' => [
            'clearCache' => false,
        ],
        // 'CheckIfComposerJsonIsReadyForUpgrade' => [],
        'AddLegacyBranch' => [
            'nameOfLegacyBranch' => 'ss5-compatible',
        ],
        'CheckoutDevMasterAndChangeToMain' => [],
        'AddUpgradeStarterBranch' => [],
        'CheckoutUpgradeStarterBranch-1' => [],
        'AddTempUpgradeBranch' => [],
        'UpgradeToSilverstripe6' => [],
        // 'UpdateComposerRequirements' => [],
        // 'UpdateComposerRequirements' => [],
        // update composer requirements to ss6 compatible versions
        // test
        'ResetWebRootDir-2' => [],
        'SwitchPhpVersion-2' => [
            'version' => '8.4',
            'alsoReinstallWithComposer' => false,
        ],
        'CheckoutTempUpgradeBranch' => [],
        // 'RunComposerInstall' => [],
        // 'RunDevBuild' => [],
        // 'RunSmokeTest' => [],
        // 'RunWriteTest' => [],
        // 'FinaliseUpgradeWithMergeIntoDefaultBranch' => [
        //     'branchToMergeInto' => 'main',
        // ],


    ];

    protected $frameworkComposerRestraint = '~5@stable';
}
